<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('produk')) {
            Schema::create('produk', function (Blueprint $table) {
                $table->bigIncrements('idproduk');
                $table->string('nama', 255);
                $table->unsignedBigInteger('idkategori');
                $table->decimal('harga', 10, 2)->default(0);
                $table->decimal('stok', 10, 2)->default(0);
                $table->enum('satuan_base', ['pcs','kg','liter','pack','unit']);
                $table->binary('gambar')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('idkategori')
                      ->references('idkategori')->on('kategori')
                      ->onUpdate('cascade')->onDelete('restrict');
            });
        }
    }
    public function down(): void { Schema::dropIfExists('produk'); }
};
