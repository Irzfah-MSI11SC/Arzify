<!doctype html>
<html lang="id" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ isset($title) ? ($title.' • ARZIFY') : 'ARZIFY' }}</title>

  <link rel="icon" type="image/png" href="{{ asset('images/arzify-logo.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="{{ asset('css/arzify.css') }}" rel="stylesheet">

  {{-- OVERRIDE POSISI NAVBAR + SIDEBAR AGAR MENYATU --}}
  <style>
    :root{
      --navbar-height: 56px;          /* default, nanti di-update via JS */
      --sidebar-width-lg: 260px;      /* lebar sidebar di layar besar */
    }

    /* NAVBAR FIXED DI ATAS */
    .app-navbar{
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1040;
    }

    /* SIDEBAR FIXED DI SAMPING (LAYAR >= LG) */
    @media (min-width: 992px){
      .sidebar-fixed-lg{
        position: fixed;
        top: var(--navbar-height);
        left: 0;
        bottom: 0;
        width: var(--sidebar-width-lg);
        overflow-y: auto;
        border-right: 1px solid var(--border, #1f2933);
      }

      main.content-with-sidebar{
        margin-left: var(--sidebar-width-lg);
        padding-top: calc(var(--navbar-height) + 1.5rem);
      }
    }

    /* DI HP / TABLET, SIDEBAR JADI OFFCANVAS BIASA */
    @media (max-width: 991.98px){
      main.content-with-sidebar{
        padding-top: calc(var(--navbar-height) + 1.5rem);
        margin-left: 0;
      }
    }
  </style>
</head>
<body class="bg-base text-base">
  <nav class="navbar navbar-dark bg-nav shadow-sm app-navbar">
    <div class="container-fluid">
      <button class="btn btn-sm btn-outline-cyan me-2 d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebarNav">
        <i class="bi bi-list"></i>
      </button>

      <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
        <img src="{{ asset('images/arzify-logo.png') }}" alt="ARZIFY" width="28" height="28" class="rounded-2">
        <span class="fw-bold brand-accent">ARZIFY</span>
      </a>

      <div class="ms-auto d-flex align-items-center gap-2">
        {{-- === Dropdown User (Ganti Password & Logout) === --}}
        <div class="dropdown">
          <button class="btn btn-outline-cyan btn-sm dropdown-toggle d-flex align-items-center gap-2"
                  type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i>
            <span class="d-none d-sm-inline">{{ session('kasir_nama') }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow" aria-labelledby="userMenu">
            <li class="px-3 py-2 text-secondary small d-sm-none">
              Halo, {{ session('kasir_nama') }}
            </li>
            <li>
              <a class="dropdown-item" href="{{ route('akun.password') }}">
                <i class="bi bi-key me-2"></i> Ganti Password
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a href="#" class="dropdown-item text-danger" id="btnLogout">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
              </a>
            </li>
          </ul>
        </div>
        {{-- === /Dropdown User === --}}
      </div>
    </div>
  </nav>

  @php
    $isTrxAll   = request()->routeIs('transaksi.*');
    $isTrxNew   = request()->routeIs('transaksi.new');
    $isTrxIndex = request()->routeIs('transaksi.index') || request()->routeIs('transaksi.show');
  @endphp

  <div class="offcanvas-lg offcanvas-start sidebar sidebar-fixed-lg bg-surface" tabindex="-1" id="sidebarNav">
    <div class="offcanvas-header d-lg-none">
      <h5 class="offcanvas-title">Menu</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 d-flex flex-column">
      <ul class="nav flex-column sidebar-menu flex-grow-1">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('dashboard')?'active':'' }}" href="{{ route('dashboard') }}">
            <i class="bi bi-speedometer2 me-2"></i><span>Dashboard</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center {{ $isTrxAll ? 'active' : '' }}"
             data-bs-toggle="collapse" href="#menuTransaksi" role="button"
             aria-expanded="{{ $isTrxAll ? 'true' : 'false' }}" aria-controls="menuTransaksi">
            <span><i class="bi bi-bag me-2"></i><span>Transaksi</span></span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <div class="collapse {{ $isTrxAll ? 'show' : '' }}" id="menuTransaksi">
            <ul class="nav flex-column ms-4 my-2">
              <li class="nav-item">
                <a class="nav-link {{ $isTrxNew ? 'active' : '' }}" href="{{ route('transaksi.new') }}">
                  <i class="bi bi-plus-circle me-2"></i><span>Transaksi Baru</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{ $isTrxIndex ? 'active' : '' }}" href="{{ route('transaksi.index') }}">
                  <i class="bi bi-receipt me-2"></i><span>Riwayat Transaksi</span>
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('produk.*')?'active':'' }}" href="{{ route('produk.index') }}">
            <i class="bi bi-box-seam me-2"></i><span>Produk</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('kategori.*')?'active':'' }}" href="{{ route('kategori.index') }}">
            <i class="bi bi-tags me-2"></i><span>Kategori</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('laporan.*')?'active':'' }}" href="{{ route('laporan.index') }}">
            <i class="bi bi-graph-up-arrow me-2"></i><span>Laporan</span>
          </a>
        </li>
      </ul>

      <div class="px-3 py-2 text-secondary small border-top border-1" style="border-color: var(--border)!important">
        © {{ date('Y') }} ARZIFY
      </div>
    </div>
  </div>

  <main class="content-with-sidebar py-4 px-3 px-lg-4">
    @if(session('success'))
      <div class="alert alert-success auto-contrast">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger auto-contrast">{{ session('error') }}</div>
    @endif

    @yield('content')
  </main>

  {{-- Form logout tersembunyi (POST /logout) --}}
  <form id="logoutForm" method="post" action="{{ route('logout') }}" class="d-none">
    @csrf
  </form>

  {{-- Modal konfirmasi logout --}}
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-surface text-base border border-secondary">
        <div class="modal-header">
          <h5 class="modal-title">Konfirmasi Logout</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          Apakah Anda yakin ingin logout?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tidak</button>
          <button type="button" class="btn btn-danger btn-sm" id="confirmLogout">Ya, Logout</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Bootstrap Bundle JS + Chart.js --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    // Hitung tinggi navbar sebenarnya -> inject ke CSS variable
    (function(){
      const nav = document.querySelector('.app-navbar');
      if(nav){
        const h = nav.offsetHeight || 56;
        document.documentElement.style.setProperty('--navbar-height', h + 'px');
      }
    })();

    // Modal logout dari item dropdown
    (function(){
      const btnLogout  = document.getElementById('btnLogout');
      const logoutForm = document.getElementById('logoutForm');
      const modalEl    = document.getElementById('logoutModal');
      const confirmBtn = document.getElementById('confirmLogout');

      if (btnLogout && logoutForm && modalEl && confirmBtn) {
        const modal = new bootstrap.Modal(modalEl, {
          backdrop: 'static',
          keyboard: false
        });

        btnLogout.addEventListener('click', (e) => {
          e.preventDefault();
          modal.show();
        });

        confirmBtn.addEventListener('click', () => {
          logoutForm.submit();
        });
      }
    })();
  </script>

  @yield('scripts')
</body>
</html>
