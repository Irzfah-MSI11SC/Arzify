@extends('layouts.app')

@section('content')
<style>
  /* MODE PRINT */
  @media print {
    .no-print,
    .navbar,
    .sidebar,
    .offcanvas,
    .offcanvas-backdrop,
    .app-navbar {
      display: none !important;
    }

    main.content-with-sidebar {
      padding: 0 !important;
      margin: 0 !important;
    }

    body {
      background: #fff !important;
      color: #000 !important;
    }

    .print-card {
      background: #fff !important;
      color: #000 !important;
      border: 1px solid #000 !important;
      box-shadow: none !important;
    }

    .print-area {
      font-size: 13px;
      line-height: 1.4;
      color: #000 !important;
    }

    .print-title {
      font-size: 16px;
      font-weight: 600;
      text-align: center;
      margin-bottom: .5rem;
      color: #000 !important;
    }

    .badge-print {
      border: 1px solid #000 !important;
      background: #fff !important;
      color: #000 !important;
      font-weight: 500;
    }

    table.table-print th,
    table.table-print td {
      color: #000 !important;
      background: transparent !important;
      border-color: #000 !important;
    }

    .row,
    .col-12,
    .col-lg-4,
    .col-lg-8 {
      float: none !important;
      width: 100% !important;
      max-width: 100% !important;
    }

    .card-header,
    .card-footer {
      background: #fff !important;
      color: #000 !important;
      border-color: #000 !important;
    }
  }
</style>

{{-- HEADER + TOMBOL --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 no-print">
  <h4 class="m-0">Detail Transaksi #{{ $trx->idtransaksi }}</h4>

  <div class="d-flex flex-wrap gap-2">
    <button class="btn btn-outline-cyan btn-sm" onclick="window.print()">
      <i class="bi bi-printer me-1"></i> Cetak Struk
    </button>

    <a href="{{ route('transaksi.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
  </div>
</div>

<div class="row g-3 print-area">
  {{-- ================== KIRI: Info transaksi ================== --}}
  <div class="col-12 col-lg-4">
    <div class="card h-100 print-card">
      <div class="card-body">
        {{-- Judul khusus saat dicetak --}}
        <div class="print-title d-none d-print-block">
          ARZIFY
          <br>Nota Transaksi
        </div>

        {{-- No Transaksi --}}
        <div class="mb-2">
          <div class="text-secondary small">No. Transaksi</div>
          <div class="fw-semibold">
            #{{ $trx->idtransaksi }}
          </div>
        </div>

        {{-- Tanggal --}}
        <div class="mb-2">
          <div class="text-secondary small">Tanggal</div>
          <div class="fw-semibold">
            {{ \Illuminate\Support\Carbon::parse($trx->tanggal)->format('d M Y H:i') }}
          </div>
        </div>

        {{-- Metode Bayar --}}
        <div class="mb-2">
          <div class="text-secondary small">Metode Bayar</div>
          <div class="fw-semibold">
            <span class="badge bg-primary text-uppercase badge-print">
              {{ strtoupper($trx->metode_bayar) }}
            </span>
          </div>
        </div>

        {{-- Kasir --}}
        @if(!empty($trx->kasir?->nama) || session('kasir_nama'))
          <div class="mb-2">
            <div class="text-secondary small">Kasir</div>
            <div class="fw-semibold">
              {{ $trx->kasir->nama ?? session('kasir_nama') ?? '-' }}
            </div>
          </div>
        @endif

        <hr class="my-3">

        {{-- TOTAL --}}
        <div class="d-flex justify-content-between align-items-start mb-1">
          <div class="fw-semibold">Total</div>
          <div class="fw-bold h5 m-0">
            Rp {{ number_format($trx->total, 0, ',', '.') }}
          </div>
        </div>

        {{-- Uang tunai & kembalian (kalau metode = tunai) --}}
        @if($trx->metode_bayar === 'tunai')
          <div class="d-flex justify-content-between small">
            <span>Uang Tunai</span>
            <span>Rp {{ number_format($trx->uang_tunai ?? 0, 0, ',', '.') }}</span>
          </div>
          <div class="d-flex justify-content-between small">
            <span>Kembalian</span>
            <span>Rp {{ number_format($trx->kembalian ?? 0, 0, ',', '.') }}</span>
          </div>
        @endif

        {{-- Total item (qty) --}}
        <div class="mt-2 small text-muted-2">
          Total Item (qty): {{ $trx->details->sum('qty') }}
        </div>
      </div>
    </div>
  </div>

  {{-- ================== KANAN: Daftar item ================== --}}
  <div class="col-12 col-lg-8">
    <div class="card h-100 print-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>  </span>
        <span class="text-secondary small d-print-none">
            
        </span>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0 table-print">
            <thead class="text-secondary">
              <tr>
                <th>Produk</th>
                <th class="text-center" style="width:100px">Qty</th>
                <th class="text-end" style="width:140px">Harga</th>
                <th class="text-end" style="width:160px">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              @foreach($trx->details as $d)
                @php
                  $namaProduk = $d->produk->nama ?? '(produk sudah dihapus)';
                @endphp
                <tr>
                  <td class="fw-semibold">
                    {{ $namaProduk }}
                    @if(!empty($d->satuan_jual))
                      <div class="text-secondary small">/ {{ $d->satuan_jual }}</div>
                    @endif
                  </td>
                  <td class="text-center">{{ $d->qty }}</td>
                  <td class="text-end">
                    Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}
                  </td>
                  <td class="text-end">
                    Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th colspan="3" class="text-end">Total</th>
                <th class="text-end">Rp {{ number_format($trx->total, 0, ',', '.') }}</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="card-footer text-center text-secondary small d-print-block">
        Terima kasih sudah berbelanja.
      </div>
    </div>
  </div>
</div>
@endsection
