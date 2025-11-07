<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'idproduk';
    public $timestamps = false;

    protected $fillable = [
        'nama',
        'idkategori',
        'harga',
        'stok',
        'satuan_base',
        'gambar', // LONGBLOB
    ];

    protected $casts = [
        'harga' => 'float',
        'stok'  => 'float',
    ];

    /** Relasi ke kategori (FK: idkategori → PK: idkategori) */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'idkategori', 'idkategori');
    }

    /** Nama tabel detail transaksi (dipakai saat guard delete) */
    public static function detailTable(): string
    {
        // Sesuaikan dengan skema DB: detail_transaksi
        return 'detail_transaksi';
    }
}
