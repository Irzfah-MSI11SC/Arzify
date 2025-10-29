@extends('layouts.app')
@section('content')
<h4 class="mb-3">Tambah Kategori</h4>
<form method="post" action="{{ route('kategori.store') }}" class="card card-body auto-contrast">
  @csrf
  <div class="mb-3">
    <label class="form-label">Nama</label>
    <input name="nama" class="form-control" required>
    @error('nama')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <button class="btn btn-accent">Simpan</button>
  <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
