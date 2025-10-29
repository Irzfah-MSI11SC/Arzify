<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'idtransaksi';
    public $timestamps = false;

    protected $fillable = [
        'idkasir', 'tanggal', 'total', 'metode_bayar'
    ];

    // >>> INI PENTING
    protected $casts = [
        'tanggal' => 'datetime', // otomatis Carbon
        'total'   => 'float',
    ];

    public function details()
    {
        return $this->hasMany(DetailTransaksi::class, 'idtransaksi', 'idtransaksi');
    }
}
