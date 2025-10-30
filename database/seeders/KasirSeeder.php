<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KasirSeeder extends Seeder
{
    public function run(): void
    {
        // Tambahan contoh kasir lain (opsional)
        DB::table('kasir')->updateOrInsert(
            ['username' => 'sakata'],
            [
                'nama'       => 'Sakata',
                'password'   => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
