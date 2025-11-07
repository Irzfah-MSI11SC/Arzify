<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        // Daftar kategori yang dipakai ProdukSeeder
        $kategori = [
            'Minuman Seduh',
            'Minuman Botol',
            'Mie Instan',
            'Bahan Pokok',
        ];

        foreach ($kategori as $nama) {
            // Jika sudah ada, biarkan; kalau belum, buat
            $exists = DB::table('kategori')->where('nama', $nama)->first();

            if (!$exists) {
                DB::table('kategori')->insert([
                    'nama' => $nama,
                ]);
            }
        }
    }
}
