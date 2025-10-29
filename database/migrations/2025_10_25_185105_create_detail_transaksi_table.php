<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('detail_transaksi')) {
            Schema::create('detail_transaksi', function (Blueprint $table) {
                $table->bigIncrements('iddetail');
                $table->unsignedBigInteger('idtransaksi');
                $table->unsignedBigInteger('idproduk');
                $table->integer('qty');
                $table->decimal('harga_satuan', 10, 2);
                $table->decimal('subtotal', 12, 2);
                $table->enum('satuan_jual', ['pcs','kg','liter','pack','unit']);
                $table->timestamps();

                $table->foreign('idtransaksi')
                      ->references('idtransaksi')->on('transaksi')
                      ->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('idproduk')
                      ->references('idproduk')->on('produk')
                      ->onUpdate('cascade')->onDelete('restrict');
            });
        }
    }
    public function down(): void { Schema::dropIfExists('detail_transaksi'); }
};
