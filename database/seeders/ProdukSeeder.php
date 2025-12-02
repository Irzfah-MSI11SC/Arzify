<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping kategori (nama => idkategori) - pastikan KategoriSeeder sudah dijalankan
        $katMap = DB::table('kategori')->pluck('idkategori', 'nama')->toArray();

        // Daftar produk contoh (kode, nama, kategori, harga, stok, satuan)
        $items = [
            ['AR101', 'Minyak Bimoli',     'Bahan Pokok',   15000,  8,  'liter'],
            ['AR102', 'Kopi Kapal Api',    'Minuman Seduh', 12000, 12,  'pcs'],
            ['AR103', 'Le Minerale 600ml', 'Minuman Botol',  5000, 24,  'pcs'],
            ['AR104', 'Indomie Goreng',    'Mie Instan',     3000, 30,  'pcs'],
            ['AR105', 'Beras',             'Bahan Pokok',   14000, 20,  'kg'],
        ];

        // path tempat gambar yang Anda siapkan
        $imageBasePath = public_path('images/products');

        // cek apakah kolom gambar ada
        $hasGambarCol = Schema::hasColumn('produk', 'gambar');

        foreach ($items as [$kode, $nama, $katNama, $harga, $stok, $satuan]) {
            $idkategori = $katMap[$katNama] ?? null;
            if (!$idkategori) {
                // Jika kategori belum ada -> skip
                $this->command->warn("Kategori \"{$katNama}\" belum ada. Lewati produk: {$nama}");
                continue;
            }

            $payload = [
                'nama'       => $nama,
                'idkategori' => $idkategori,
                'harga'      => (float) $harga,
                'stok'       => (float) $stok,
            ];

            // jika tabel produk punya kolom satuan_base
            if (Schema::hasColumn('produk', 'satuan_base')) {
                $payload['satuan_base'] = $satuan;
            }

            // Bila kolom gambar ada, coba muat gambar dan compress agar muat
            if ($hasGambarCol) {
                $filename = "{$kode}.JPG";
                $imgPath = $imageBasePath . DIRECTORY_SEPARATOR . $filename;

                if (file_exists($imgPath)) {
                    // ambil limit kolom (byte)
                    $limit = $this->blobLimit('produk', 'gambar');

                    // siapkan binari gambar yang sudah dikompres
                    $bin = $this->prepareBlobImage($imgPath, $limit);

                    if ($bin !== null && strlen($bin) <= $limit) {
                        $payload['gambar'] = $bin;
                        $this->command->info("Gambar dimasukkan untuk {$kode} (".round(strlen($bin)/1024,2)." KB)");
                    } else {
                        // kalau gagal muat walaupun sudah dikompres, skip menyimpan gambar
                        $payload['gambar'] = null;
                        $this->command->warn("Gagal membuat gambar muat untuk {$kode}. Gambar tidak disimpan (disimpan NULL).");
                    }
                } else {
                    $payload['gambar'] = null;
                    $this->command->warn("File gambar tidak ditemukan: {$imgPath} (produk {$kode} tanpa gambar).");
                }
            }

            // kriteria unik: pakai kode kalau ada field kode, kalau enggak pakai nama
            $where = Schema::hasColumn('produk', 'kode') ? ['kode' => $kode] : ['nama' => $nama];

            // jika field kode ada, sertakan ke payload
            if (Schema::hasColumn('produk', 'kode')) {
                $payload['kode'] = $kode;
            }

            DB::table('produk')->updateOrInsert($where, $payload);
        }
    }

    /**
     * Dapatkan batas maksimal byte tipe BLOB untuk kolom tertentu.
     */
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
     * Return binary string atau null jika gagal.
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

        // buat resource sumber berdasarkan mime
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

        // coba beberapa iterasi: turunkan resolusi dan kualitas secara bertahap
        for ($i = 0; $i < 10; $i++) {
            if ($i > 0) {
                $targetW = max(320, (int) round($targetW * 0.8));
                $targetH = max(320, (int) round($targetH * 0.8));
            }

            $dst = imagecreatetruecolor($targetW, $targetH);

            // preserve transparency for PNG/WebP
            if ($mimeIn === 'image/png' && function_exists('imagealphablending')) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $targetW, $targetH, $w, $h);

            ob_start();
            if ($canWebp) {
                imagewebp($dst, null, $qWebp);
            } else {
                imagejpeg($dst, null, $qJpg);
            }
            $bin = ob_get_clean();

            imagedestroy($dst);

            if ($bin !== false && strlen($bin) <= $maxBytes) {
                imagedestroy($src);
                return $bin;
            }

            if ($canWebp && $qWebp > 40) $qWebp -= 8;
            if (!$canWebp && $qJpg > 40) $qJpg -= 5;
        }

        imagedestroy($src);
        return null;
    }
}
