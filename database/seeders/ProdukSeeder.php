<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        // Map kategori (nama -> idkategori) setelah KategoriSeeder dijalankan
        $katMap = DB::table('kategori')->pluck('idkategori', 'nama');

        // Cek kolom opsional agar fleksibel dengan skema kamu
        $hasKode       = Schema::hasColumn('produk', 'kode');          // jika punya kolom 'kode'
        $hasSatuanBase = Schema::hasColumn('produk', 'satuan_base');   // enum('pcs','kg','liter') di proyekmu

        // Data dari gambar
        $items = [
            // kode, nama, kategori, harga, stok, satuan
            ['AR101', 'Minyak Bimoli',       'Bahan Pokok',      15000,  8,  'liter'],
            ['AR102', 'Kopi Kapal Api',      'Minuman Seduh',        12000, 12,  'pcs'],
            ['AR103', 'Le Minerale 600ml',   'Minuman Botol',  5000, 24,  'pcs'],
            ['AR104', 'Indomie Goreng',      'Mie Instan',   3000, 30,  'pcs'],
            ['AR105', 'Beras',               'Bahan Pokok',       14000, 20,  'kg'],
        ];

        foreach ($items as [$kode, $nama, $katNama, $harga, $stok, $satuan]) {
            $idkategori = $katMap[$katNama] ?? null;
            if (!$idkategori) {
                // Jika kategori belum ada karena perubahan, lewati item ini
                continue;
            }

            $data = [
                'nama'       => $nama,
                'idkategori' => $idkategori,
                'harga'      => (float) $harga,
                'stok'       => (float) $stok,
            ];
            if ($hasSatuanBase) {
                $data['satuan_base'] = $satuan; // 'pcs' | 'kg' | 'liter'
            }

            // Kriteria unik: pakai 'kode' jika ada; kalau tidak ada, pakai 'nama'
            $where = $hasKode ? ['kode' => $kode] : ['nama' => $nama];

            // Simpan (insert kalau belum ada, update kalau ada)
            $payload = $data;
            if ($hasKode) $payload['kode'] = $kode;

            DB::table('produk')->updateOrInsert($where, $payload);
        }
    }
}
