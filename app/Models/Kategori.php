<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use SoftDeletes;

    protected $table = 'kategori';
    protected $primaryKey = 'idkategori';
    // public $timestamps = false; // uncomment jika tabel tidak pakai timestamps

    protected $fillable = ['nama'];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function produk()
    {
        // include produk yang terhapus supaya bisa dicek bila perlu
        return $this->hasMany(Produk::class, 'idkategori', 'idkategori');
    }
}