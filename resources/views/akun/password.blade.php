@extends('layouts.app')
@section('content')
<h4 class="mb-3">Ganti Password</h4>
<form method="post" action="{{ route('akun.password.update') }}" class="card card-body auto-contrast" style="max-width:460px">
  @csrf
  <div class="mb-3">
    <label class="form-label">Password Baru</label>
    <input type="password" name="password" class="form-control" required>
    @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
  </div>
  <div class="mb-3">
    <label class="form-label">Ulangi Password Baru</label>
    <input type="password" name="password_confirmation" class="form-control" required>
  </div>
  <button class="btn btn-accent">Perbarui</button>
</form>
@endsection
