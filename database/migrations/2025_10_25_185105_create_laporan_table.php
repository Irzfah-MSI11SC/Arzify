<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('laporan')) {
            Schema::create('laporan', function (Blueprint $table) {
                $table->bigIncrements('idlaporan');
                $table->unsignedBigInteger('idtransaksi')->nullable();
                $table->enum('tipe_periode', ['Harian','Mingguan','Bulanan','Tahunan']);
                $table->integer('total_transaksi')->default(0);
                $table->decimal('total_pendapatan', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('idtransaksi')
                      ->references('idtransaksi')->on('transaksi')
                      ->onUpdate('cascade')->onDelete('set null');
            });
        }
    }
    public function down(): void { Schema::dropIfExists('laporan'); }
};
