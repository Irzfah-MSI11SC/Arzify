<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    // >>> SESUAIKAN DENGAN NAMA TABEL ASLI DI DATABASE <<<
    // kalau tabel kamu adalah `detail_transaksi`, pakai ini:
    protected $table = 'detail_transaksi';

    // kalau ternyata di DB kamu namanya `detailtransaksi`, ubah ke:
    // protected $table = 'detailtransaksi';

    // primary key tabel detail (lihat di phpMyAdmin kolom mana yg jadi PRIMARY)
    protected $primaryKey = 'iddetail'; 
    // kalau pk kamu namanya beda (misal iddetailtransaksi) ubah di sini

    public $timestamps = false;

    protected $fillable = [
        'idtransaksi',
        'idproduk',
        'qty',
        'harga_satuan',
        'subtotal',
        'satuan_jual',
    ];

    // setiap baris detail milik 1 produk
    public function produk()
    {
        // idproduk di detail_transaksi -> idproduk di produk
        return $this->belongsTo(Produk::class, 'idproduk', 'idproduk');
    }

    // setiap baris detail milik 1 transaksi
    public function transaksi()
    {
        // idtransaksi di detail_transaksi -> idtransaksi di transaksi
        return $this->belongsTo(Transaksi::class, 'idtransaksi', 'idtransaksi');
    }
}
