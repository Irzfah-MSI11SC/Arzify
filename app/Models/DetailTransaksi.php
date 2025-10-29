<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'iddetail';
    public $timestamps = false;

    protected $fillable = ['idtransaksi','idproduk','qty','harga_satuan','subtotal','satuan_jual'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'idtransaksi', 'idtransaksi');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'idproduk', 'idproduk');
    }
}
