<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'idtransaksi';
    public $timestamps = false;

    protected $fillable = [
        'idkasir',
        'tanggal',
        'total',
        'metode_bayar',
        'uang_tunai',   // ✅ baru
        'kembalian',    // ✅ baru
    ];

    protected $casts = [
        'total'      => 'float',
        'uang_tunai' => 'float',
        'kembalian'  => 'float',
    ];

    public function details()
    {
        return $this->hasMany(DetailTransaksi::class, 'idtransaksi', 'idtransaksi');
    }

    public function kasir()
    {
        return $this->belongsTo(Kasir::class, 'idkasir', 'idkasir');
    }
}
