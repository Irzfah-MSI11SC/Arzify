<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';        // nama tabel transaksi
    protected $primaryKey = 'idtransaksi'; // pk transaksi (lihat DB)
    public $timestamps = false;

    protected $fillable = [
        'idkasir',
        'tanggal',
        'total',
        'metode_bayar',
        // kalau kamu punya kolom bayar/kembalian nanti bisa ditambah di sini
    ];

    // satu transaksi punya banyak detail_transaksi
    public function details()
    {
        // hasMany(ModelTujuan, fk_di_tabel_detail, pk_di_tabel_ini)
        return $this->hasMany(DetailTransaksi::class, 'idtransaksi', 'idtransaksi');
    }

    // siapa kasir yg handle transaksi ini
    public function kasir()
    {
        // asumsinya tabel kasir = 'kasir', pk = 'idkasir'
        return $this->belongsTo(Kasir::class, 'idkasir', 'idkasir');
    }
}
