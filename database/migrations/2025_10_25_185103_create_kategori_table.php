<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('kategori')) {
            Schema::create('kategori', function (Blueprint $table) {
                $table->bigIncrements('idkategori');
                $table->string('nama', 255)->unique();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
    public function down(): void { Schema::dropIfExists('kategori'); }
};
    