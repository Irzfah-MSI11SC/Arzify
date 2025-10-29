@extends('layouts.app')
@section('content')
<div class="d-flex mb-3 justify-content-between align-items-center">
  <h4 class="m-0">Kategori</h4>
  <a href="{{ route('kategori.create') }}" class="btn btn-accent">Tambah</a>
</div>
<div class="card">
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle m-0">
      <thead><tr><th>#</th><th>Nama</th><th class="text-end">Aksi</th></tr></thead>
      <tbody>
        @foreach($kategori as $k)
          <tr>
            <td>{{ $k->idkategori }}</td>
            <td>{{ $k->nama }}</td>
            <td class="text-end">
              <a href="{{ route('kategori.edit',$k) }}" class="btn btn-sm btn-outline-cyan">Ubah</a>
              <form action="{{ route('kategori.destroy',$k) }}" method="post" class="d-inline" onsubmit="return confirm('Hapus kategori?')">
                @csrf @method('delete')
                <button class="btn btn-sm btn-outline-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
<div class="mt-3">{{ $kategori->withQueryString()->links() }}</div>
@endsection
