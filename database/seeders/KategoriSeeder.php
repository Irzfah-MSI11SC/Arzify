<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['nama' => 'Sembako'],
            ['nama' => 'Minuman'],
            ['nama' => 'Snack'],
            ['nama' => 'Perawatan'],
        ];

        foreach ($rows as $r) {
            DB::table('kategori')->updateOrInsert(
                ['nama' => $r['nama']],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
