<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'idkategori';
    public $timestamps = false;

    protected $fillable = ['nama'];

    /** Relasi balik: satu kategori punya banyak produk */
    public function produk()
    {
        return $this->hasMany(Produk::class, 'idkategori', 'idkategori');
    }
}
