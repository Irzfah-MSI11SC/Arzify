<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProdukController extends Controller
{
    /* ========================= LIST ========================= */
    public function index(Request $r)
{
    $q = trim((string) $r->q);

    $produk = Produk::query()
        ->with(['kategori:idkategori,nama'])               // ← eager load kategori (hindari N+1)
        ->when($q !== '', fn ($qq) => $qq->where('nama', 'like', "%{$q}%"))
        ->orderBy('nama')
        ->paginate(12)
        ->withQueryString();

    return view('produk.index', [
        'title'  => 'Produk',
        'produk' => $produk,
        'q'      => $q,
    ]);
}


    /* ===================== FORM CREATE ====================== */
    public function create()
    {
        $kategori = Kategori::orderBy('nama')->get(['idkategori', 'nama']);
        return view('produk.create', [
            'title'    => 'Tambah Produk',
            'kategori' => $kategori,
        ]);
    }

    /* ======================= STORE ========================== */
    public function store(Request $r)
    {
        $valid = $r->validate([
            'nama'        => ['required', 'string', 'max:100'],
            'idkategori'  => ['required', 'integer'],
            'harga'       => ['required'],                        // akan dinormalisasi
            'stok'        => ['required', 'numeric', 'min:0'],    // desimal OK
            'satuan_base' => ['nullable', 'in:pcs,kg,liter'],
            'gambar'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'], // 50MB
        ]);

        $data = [
            'nama'       => $valid['nama'],
            'idkategori' => (int) $valid['idkategori'],
            'harga'      => $this->normalizeHarga($valid['harga']),
            'stok'       => $this->normalizeHarga($valid['stok']),
        ];

        if (Schema::hasColumn('produk', 'satuan_base')) {
            $data['satuan_base'] = $valid['satuan_base'] ?? null;
        }

        // Gambar ke LONGBLOB (kompres/downscale bila perlu)
        if ($r->hasFile('gambar') && $r->file('gambar')->isValid()) {
            $limit = $this->blobLimit('produk', 'gambar');
            $blob  = $this->prepareBlobImage($r->file('gambar')->getRealPath(), $limit)
                  ?? @file_get_contents($r->file('gambar')->getRealPath());

            if ($blob !== false && $blob !== null && strlen($blob) <= $limit) {
                $data['gambar'] = $blob;
            } else {
                return back()->withInput()->with('error', 'Gambar terlalu besar untuk tipe kolom saat ini. Unggah gambar yang lebih kecil.');
            }
        }

        $data = $this->filterColumns('produk', $data);
        Produk::create($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $produk   = Produk::findOrFail($id);
        $kategori = Kategori::orderBy('nama')->get(['idkategori', 'nama']);

        return view('produk.edit', [
            'title'    => 'Ubah Produk',
            'produk'   => $produk,
            'kategori' => $kategori,
        ]);
    }
    public function update(Request $r, $id)
    {
        $p = Produk::findOrFail($id);

        $valid = $r->validate([
            'nama'        => ['required', 'string', 'max:100'],
            'idkategori'  => ['required', 'integer'],
            'harga'       => ['required'],
            'stok'        => ['required', 'numeric', 'min:0'],
            'satuan_base' => ['nullable', 'in:pcs,kg,liter'],
            'gambar'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
        ]);

        $data = [
            'nama'       => $valid['nama'],
            'idkategori' => (int) $valid['idkategori'],
            'harga'      => $this->normalizeHarga($valid['harga']),
            'stok'       => $this->normalizeHarga($valid['stok']),
        ];

        if (Schema::hasColumn('produk', 'satuan_base')) {
            $data['satuan_base'] = $valid['satuan_base'] ?? null;
        }

        if ($r->hasFile('gambar') && $r->file('gambar')->isValid()) {
            $limit = $this->blobLimit('produk', 'gambar');
            $blob  = $this->prepareBlobImage($r->file('gambar')->getRealPath(), $limit)
                  ?? @file_get_contents($r->file('gambar')->getRealPath());

            if ($blob !== false && $blob !== null && strlen($blob) <= $limit) {
                $data['gambar'] = $blob;
            } else {
                return back()->withInput()->with('error', 'Gambar terlalu besar untuk tipe kolom saat ini. Unggah gambar yang lebih kecil.');
            }
        }

        $data = $this->filterColumns('produk', $data);
        $p->update($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diubah.');
    }

    /* ======================== DESTROY ======================= */
    public function destroy($id)
    {
        $p = Produk::findOrFail($id);

        // Kumpulkan kandidat nama tabel detail transaksi
        $candidates = [];
        if (method_exists(Produk::class, 'detailTable')) {
            try {
                $t = Produk::detailTable();
                if (is_string($t) && $t !== '') $candidates[] = $t;
            } catch (\Throwable $e) { /* abaikan */ }
        }

        // Tambahkan beberapa nama umum
        $candidates = array_values(array_unique(array_merge($candidates, [
            'detail_transaksi',
            'detailtransaksi',
            'detail_transaksis',
            'detailtransaksis',
        ])));

        // Kemungkinan nama kolom relasi ke produk
        $colCandidates = ['idproduk', 'produk_id', 'id_produk'];

        $dipakai = false;

        foreach ($candidates as $tbl) {
            if (!Schema::hasTable($tbl)) continue;

            // Cari kolom yang ada
            $col = null;
            foreach ($colCandidates as $c) {
                if (Schema::hasColumn($tbl, $c)) { $col = $c; break; }
            }
            if (!$col) continue;

            try {
                $dipakai = DB::table($tbl)->where($col, $p->idproduk ?? $p->id)->limit(1)->exists();
            } catch (\Throwable $e) {
                $dipakai = false;
            }

            if ($dipakai) break;
        }

        if ($dipakai) {
            return back()->with('error', 'Produk tidak bisa dihapus karena sudah dipakai pada transaksi.');
        }

        $p->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }

    /* ======================== HELPERS ======================= */

    private function normalizeHarga($val): float
    {
        // "Rp 120.000,50" → 120000.50  |  "1.5" → 1.5
        $s = trim((string) $val);
        $s = str_ireplace(['rp', 'idr', ' '], '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        return (float) $s;
    }

    private function filterColumns(string $table, array $data): array
    {
        $cols = Schema::getColumnListing($table);
        return array_intersect_key($data, array_flip($cols));
    }

    /** Dapatkan batas maksimal byte tipe BLOB untuk kolom tertentu. */
    private function blobLimit(string $table, string $column): int
    {
        $db = DB::getDatabaseName();

        $row = DB::table('INFORMATION_SCHEMA.COLUMNS')
            ->select('DATA_TYPE')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->first();

        $type = strtolower($row->DATA_TYPE ?? 'longblob');

        return match ($type) {
            'tinyblob'   => 255,
            'blob'       => 65535,        // ~64 KB
            'mediumblob' => 16777215,     // ~16 MB
            'longblob'   => 4294967295,   // ~4 GB
            default      => 65535,
        };
    }

    /**
     * Kompres + downscale gambar sampai ukuran <= $maxBytes.
     * Return binary string atau null jika masih terlalu besar / gagal dibaca.
     */
    private function prepareBlobImage(string $path, int $maxBytes): ?string
    {
        $origBin = @file_get_contents($path);
        if ($origBin === false) return null;
        if (strlen($origBin) <= $maxBytes) return $origBin;

        $info = @getimagesize($path);
        if ($info === false) return null;

        [$w, $h] = [$info[0], $info[1]];
        $mimeIn  = $info['mime'] ?? 'image/jpeg';

        // buat resource sumber
        switch ($mimeIn) {
            case 'image/png'  : $src = @imagecreatefrompng($path);  break;
            case 'image/webp' : $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null; break;
            default           : $src = @imagecreatefromjpeg($path); break;
        }
        if (!$src) return null;

        $targetW = $w;
        $targetH = $h;
        $canWebp = function_exists('imagewebp');
        $qJpg    = 85;
        $qWebp   = 80;

        for ($i = 0; $i < 8; $i++) {
            if ($i > 0) {
                $targetW = max(320, (int) round($targetW * 0.8));
                $targetH = max(320, (int) round($targetH * 0.8));
            }

            $dst = imagecreatetruecolor($targetW, $targetH);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $targetH, $w, $h);

            ob_start();
            if ($canWebp) imagewebp($dst, null, $qWebp);
            else          imagejpeg($dst, null, $qJpg);
            $bin = ob_get_clean();

            imagedestroy($dst);

            if ($bin !== false && strlen($bin) <= $maxBytes) {
                imagedestroy($src);
                return $bin;
            }

            if ($canWebp && $qWebp > 50) $qWebp -= 8;
            if (!$canWebp && $qJpg > 60) $qJpg -= 5;
        }

        imagedestroy($src);
        return null;
    }
}
