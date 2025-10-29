<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | Arzify</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="{{ asset('css/arzify.css') }}" rel="stylesheet">
</head>
<body class="bg-base d-flex align-items-center" style="min-height:100vh;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-10 col-md-6 col-lg-5">
        <div class="card card-glow p-4">
          <h3 class="text-center mb-3 brand-accent">ARZIFY</h3>
          <p class="text-center text-secondary mb-4">Masuk ke sistem kasir</p>
          @if(session('error')) <div class="alert alert-danger auto-contrast">{{ session('error') }}</div> @endif
          <form method="post" action="{{ route('login.post') }}" class="auto-contrast">
            @csrf
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input name="username" class="form-control" value="{{ old('username') }}" required>
              @error('username')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
              @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-accent w-100">Masuk</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
