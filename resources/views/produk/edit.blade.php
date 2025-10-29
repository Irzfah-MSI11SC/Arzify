@extends('layouts.app')
@section('content')
<h4 class="mb-3">Ubah Produk</h4>
<form method="post" action="{{ route('produk.update',$produk) }}" enctype="multipart/form-data" class="card card-body auto-contrast">
  @csrf @method('put')
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nama</label>
      <input name="nama" class="form-control" value="{{ old('nama',$produk->nama) }}" required>
      @error('nama')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
      <label class="form-label">Kategori</label>
      <select name="idkategori" class="form-select" required>
        @foreach($kategori as $k) 
          <option value="{{ $k->idkategori }}" @selected($produk->idkategori==$k->idkategori)>{{ $k->nama }}</option> 
        @endforeach
      </select>
      @error('idkategori')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-4">
      <label class="form-label">Harga</label>
      <input name="harga" type="number" min="0" step="0.01" class="form-control" value="{{ $produk->harga }}" required>
      @error('harga')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-4">
      <label class="form-label">Stok</label>
      <input name="stok" type="number" min="0" step="1" class="form-control" value="{{ $produk->stok }}" required>
      @error('stok')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">Satuan</label>
      <select name="satuan_base" class="form-select" required>
        @foreach(['pcs','kg','liter','pack','unit'] as $u)
          <option value="{{ $u }}" @selected($produk->satuan_base==$u)>{{ strtoupper($u) }}</option>
        @endforeach
      </select>
      @error('satuan_base')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
      <label class="form-label">Gambar (opsional)</label>
      <input type="file" name="gambar" accept="image/*" class="form-control">
      @error('gambar')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-accent">Simpan Perubahan</button>
    <a class="btn btn-secondary" href="{{ route('produk.index') }}">Batal</a>
  </div>
</form>
@endsection
