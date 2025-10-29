<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdukSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil idkategori berdasarkan nama
        $kat = DB::table('kategori')->pluck('idkategori', 'nama');

        $data = [
            [
                'nama'        => 'Beras',
                'idkategori'  => $kat['Sembako'] ?? null,
                'harga'       => 15000,
                'stok'        => 100,
                'satuan_base' => 'pcs', // atau kg jika skema kamu memakai kg
            ],
            [
                'nama'        => 'Minyak Goreng 1L',
                'idkategori'  => $kat['Sembako'] ?? null,
                'harga'       => 18000,
                'stok'        => 80,
                'satuan_base' => 'pcs',
            ],
            [
                'nama'        => 'Air Mineral 600ml',
                'idkategori'  => $kat['Minuman'] ?? null,
                'harga'       => 4000,
                'stok'        => 200,
                'satuan_base' => 'pcs',
            ],
            [
                'nama'        => 'Keripik Kentang',
                'idkategori'  => $kat['Snack'] ?? null,
                'harga'       => 12000,
                'stok'        => 60,
                'satuan_base' => 'pcs',
            ],
        ];

        foreach ($data as $row) {
            // Lewatkan jika idkategori belum ada (misal kategori belum dibuat)
            if (!$row['idkategori']) { continue; }

            DB::table('produk')->updateOrInsert(
                ['nama' => $row['nama']],
                [
                    'idkategori'  => $row['idkategori'],
                    'harga'       => $row['harga'],
                    'stok'        => $row['stok'],
                    'satuan_base' => $row['satuan_base'],
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }
    }
}
