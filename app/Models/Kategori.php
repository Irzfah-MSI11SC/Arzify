<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table      = 'kategori';     // nama tabel
    protected $primaryKey = 'idkategori';   // primary key
    public $timestamps    = false;

    protected $fillable = ['nama'];

    // Relasi: satu kategori punya banyak produk
    public function produk()
    {
        return $this->hasMany(Produk::class, 'idkategori', 'idkategori');
    }
}
