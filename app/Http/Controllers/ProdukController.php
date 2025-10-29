<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Produk;
use App\Models\Kategori;

class ProdukController extends Controller
{
    /**
     * LIST / SEARCH
     */
    public function index(Request $r)
    {
        $q = trim((string) $r->q);

        $produk = Produk::query()
            ->with('kategori')                          // untuk menampilkan nama kategori
            ->when($q !== '', function ($qq) use ($q) { // pencarian by nama
                $qq->where('nama', 'like', "%{$q}%");
            })
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString();

        return view('produk.index', [
            'title'  => 'Produk',
            'produk' => $produk,
            'q'      => $q,
        ]);
    }

    /**
     * FORM TAMBAH
     */
    public function create()
    {
        $kategori = Kategori::orderBy('nama')->get();

        return view('produk.create', [
            'title'    => 'Tambah Produk',
            'kategori' => $kategori,
        ]);
    }

    /**
     * SIMPAN PRODUK
     */
    public function store(Request $r)
    {
        $r->validate([
            'nama'       => 'required|string|max:100',
            'idkategori' => 'required|integer|exists:kategori,idkategori',
            'harga'      => 'required|numeric|min:0',
            'stok'       => 'required|numeric|min:0',
            'satuan_base'=> 'nullable|string|max:10',
            'gambar'     => 'nullable|image|max:2048',
        ]);

        $data = [
            'nama'       => $r->nama,
            'idkategori' => (int) $r->idkategori,
            'harga'      => (float) $r->harga,
            'stok'       => (float) $r->stok,
        ];

        if (Schema::hasColumn('produk', 'satuan_base')) {
            $data['satuan_base'] = $r->satuan_base ?: 'pcs';
        }

        if (Schema::hasColumn('produk', 'gambar') && $r->hasFile('gambar')) {
            $data['gambar'] = file_get_contents($r->file('gambar')->getRealPath());
        }

        Produk::create($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * FORM UBAH
     */
    public function edit($id)
    {
        $produk   = Produk::findOrFail($id);
        $kategori = Kategori::orderBy('nama')->get();

        return view('produk.edit', [
            'title'    => 'Ubah Produk',
            'produk'   => $produk,
            'kategori' => $kategori,
        ]);
    }

    /**
     * SIMPAN PERUBAHAN
     */
    public function update(Request $r, $id)
    {
        $r->validate([
            'nama'       => 'required|string|max:100',
            'idkategori' => 'required|integer|exists:kategori,idkategori',
            'harga'      => 'required|numeric|min:0',
            'stok'       => 'required|numeric|min:0',
            'satuan_base'=> 'nullable|string|max:10',
            'gambar'     => 'nullable|image|max:2048',
        ]);

        $p = Produk::findOrFail($id);

        $data = [
            'nama'       => $r->nama,
            'idkategori' => (int) $r->idkategori,
            'harga'      => (float) $r->harga,
            'stok'       => (float) $r->stok,
        ];

        if (Schema::hasColumn('produk', 'satuan_base')) {
            $data['satuan_base'] = $r->satuan_base ?: ($p->satuan_base ?? 'pcs');
        }

        if (Schema::hasColumn('produk', 'gambar') && $r->hasFile('gambar')) {
            $data['gambar'] = file_get_contents($r->file('gambar')->getRealPath());
        }

        $p->update($data);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diubah.');
    }

    /**
     * HAPUS – ditolak bila pernah dipakai transaksi
     */
    public function destroy($id)
    {
        $p = Produk::findOrFail($id);

        $dipakai = DB::table('detail_transaksi')->where('idproduk', $p->idproduk)->exists();
        if ($dipakai) {
            return back()->with('error', 'Produk tidak bisa dihapus karena sudah dipakai pada transaksi.');
        }

        $p->delete();
        return back()->with('success', 'Produk dihapus.');
    }
}
