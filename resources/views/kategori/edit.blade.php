@extends('layouts.app')
@section('content')
<h4 class="mb-3">Ubah Kategori</h4>
<form method="post" action="{{ route('kategori.update',$kategori) }}" class="card card-body auto-contrast">
  @csrf @method('put')
  <div class="mb-3">
    <label class="form-label">Nama</label>
    <input name="nama" class="form-control" value="{{ old('nama',$kategori->nama) }}" required>
    @error('nama')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-accent">Simpan Perubahan</button>
  <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
