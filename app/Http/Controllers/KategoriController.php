<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::orderBy('nama')->paginate(10);
        return view('kategori.index', compact('kategori'));
    }

    public function create()
    {
        return view('kategori.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate(['nama'=>'required|string|max:255|unique:kategori,nama']);
        Kategori::create($data);
        return redirect()->route('kategori.index')->with('success','Kategori ditambahkan.');
    }

    public function edit(Kategori $kategori)
    {
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $r, Kategori $kategori)
    {
        $data = $r->validate(['nama'=>'required|string|max:255|unique:kategori,nama,'.$kategori->idkategori.',idkategori']);
        $kategori->update($data);
        return redirect()->route('kategori.index')->with('success','Perubahan disimpan.');
    }

    public function destroy(Kategori $kategori)
    {
        if ($kategori->produk()->exists()) {
            return back()->with('error','Kategori masih dipakai produk.');
        }
        $kategori->delete();
        return back()->with('success','Kategori dihapus.');
    }
}
