<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            KasirSeeder::class,
            KategoriSeeder::class,
            ProdukSeeder::class,
            // Hapus komentar baris di bawah jika ingin buat transaksi contoh sekalian
            // DummyTransaksiSeeder::class,
        ]);
    }
}
