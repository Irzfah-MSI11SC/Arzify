<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    use SoftDeletes;

    protected $table = 'produk';
    protected $primaryKey = 'idproduk';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; // ubah ke true jika tabel punya timestamps created_at/updated_at

    protected $fillable = [
        'nama',
        'idkategori',
        'harga',
        'stok',
        'satuan_base',
        'gambar',
    ];

    protected $casts = [
        'harga' => 'float',
        'stok'  => 'float',
    ];

    // relasi ke kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'idkategori', 'idkategori');
    }

    // nama tabel detail (dipakai di controller pengecekan)
    public static function detailTable(): string
    {
        return 'detailtransaksi';
    }
}