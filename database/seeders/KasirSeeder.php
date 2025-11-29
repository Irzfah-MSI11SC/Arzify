<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KasirSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'username'   => 'sakata',
                'nama'       => 'Sakata',
                'password'   => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username'   => 'irjon',
                'nama'       => 'Irjon',
                'password'   => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($data as $kasir) {
            DB::table('kasir')->updateOrInsert(
                ['username' => $kasir['username']],  // kondisi pencarian
                $kasir                                // data yang disimpan
            );
        }
    }
}
