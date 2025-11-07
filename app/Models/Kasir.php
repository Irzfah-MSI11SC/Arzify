<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kasir extends Model
{
    protected $table = 'kasir';
    protected $primaryKey = 'idkasir';
    public $timestamps = false;               // tabel kasir kamu tidak punya created_at/updated_at
    protected $fillable = ['username','password','nama'];
    protected $hidden   = ['password'];
}
