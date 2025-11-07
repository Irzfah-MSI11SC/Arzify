@extends('layouts.app')

@section('content')
<h4 class="mb-3">Tambah Produk</h4>

<form method="post" action="{{ route('produk.store') }}" enctype="multipart/form-data" class="card card-body">
  @csrf

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Nama</label>
      <input name="nama" value="{{ old('nama') }}" class="form-control" required>
      @error('nama') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
      <label class="form-label">Kategori</label>
      <select name="idkategori" class="form-select" required>
        <option value="">— pilih —</option>
        @foreach($kategori as $k)
          <option value="{{ $k->idkategori }}" {{ old('idkategori') == $k->idkategori ? 'selected' : '' }}>
            {{ $k->nama }}
          </option>
        @endforeach
      </select>
      @error('idkategori') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
      <label class="form-label">Harga</label>
      <input type="number" step="1" min="0" name="harga" value="{{ old('harga', 0) }}" class="form-control" required>
      @error('harga') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
      <label class="form-label">Stok</label>
      <input type="number" step="0.01" min="0" name="stok" value="{{ old('stok', 0) }}" class="form-control" required>
      @error('stok') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
  <label class="form-label">Satuan</label>
  @php
    $satuanOpts = ['pcs','kg','liter'];
    $sel = old('satuan_base');
  @endphp
  <select name="satuan_base" class="form-select">
    <option value="">— pilih satuan —</option>
    @foreach($satuanOpts as $v)
      <option value="{{ $v }}" {{ $sel === $v ? 'selected' : '' }}>
        {{ strtoupper($v) }}
      </option>
    @endforeach
  </select>
  @error('satuan_base') <div class="text-danger small">{{ $message }}</div> @enderror
</div>


    <div class="col-md-6">
      <label class="form-label">Gambar (opsional)</label>
      <input type="file" name="gambar" class="form-control">
      <div class="form-text">Maks 2MB, JPG/PNG.</div>
      @error('gambar') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
  </div>

  <div class="mt-3 d-flex gap-2">
    <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-accent">Simpan</button>
  </div>
</form>
@endsection
