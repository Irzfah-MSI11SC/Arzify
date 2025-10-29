<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kasir extends Model
{
    protected $table = 'kasir';
    protected $primaryKey = 'idkasir';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = ['username', 'password', 'nama'];
    protected $hidden = ['password'];

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'idkasir', 'idkasir');
    }
}
