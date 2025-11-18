<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('transaksi')) {
            Schema::create('transaksi', function (Blueprint $table) {
                $table->bigIncrements('idtransaksi');
                $table->unsignedBigInteger('idkasir');
                $table->dateTime('tanggal');
                $table->decimal('total', 15, 2)->default(0);
                $table->enum('metode_bayar', ['tunai','qris']);
                $table->timestamps();
                $table->decimal('uang_tunai', 15, 2)->nullable();
            $table->decimal('kembalian', 15, 2)->default(0);

                $table->foreign('idkasir')
                      ->references('idkasir')->on('kasir')
                      ->onUpdate('cascade')->onDelete('restrict');
            });
        }
    }
    public function down(): void { Schema::dropIfExists('transaksi'); }
};
