<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table      = 'produk';      // nama tabel
    protected $primaryKey = 'idproduk';    // primary key
    public $timestamps    = false;         // set true kalau ada created_at/updated_at

    // kolom yang boleh diisi mass-assignment
    protected $fillable = [
        'nama', 'idkategori', 'harga', 'stok', 'satuan_base', 'gambar'
    ];

    // Relasi: setiap produk milik satu kategori
    public function kategori()
    {
        // 'idkategori' di produk mengacu ke 'idkategori' di tabel kategori
        return $this->belongsTo(Kategori::class, 'idkategori', 'idkategori');
    }

    // (opsional) scope aktif, biar tidak error kalau dipanggil
    public function scopeAktif($q)
    {
        return $q;
        // kalau ingin filter stok > 0:
        // return $q->where('stok', '>', 0);
    }
}
