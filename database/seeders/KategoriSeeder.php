<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            'Minuman Seduh',
            'Minuman Botol',
            'Mie Instan',
            'Bahan Pokok',
        ];

        foreach ($kategori as $nama) {
            $exists = DB::table('kategori')->where('nama', $nama)->first();

            if (!$exists) {
                DB::table('kategori')->insert([
                    'nama' => $nama,
                ]);
            }
        }
    }
}
