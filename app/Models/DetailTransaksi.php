<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    // pastikan ini sesuai nama tabel di database: "detail_transaksi"
    protected $table = 'detail_transaksi';

    public $timestamps = true; // kalau tabel punya created_at/updated_at; ubah false jika tidak

    protected $fillable = [
        'idtransaksi',
        'idproduk',
        'qty',
        'harga_satuan',
        'subtotal',
        'satuan_jual',
    ];

    protected $casts = [
        'qty' => 'integer',
        'harga_satuan' => 'float',
        'subtotal' => 'float',
    ];

    // relasi ke produk (pakai withTrashed jika produk soft-deleted)
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'idproduk', 'idproduk')->withTrashed();
    }

    // **PENTING**: relasi ke transaksi — diperlukan untuk whereHas di dashboard
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'idtransaksi', 'idtransaksi');
    }
}
