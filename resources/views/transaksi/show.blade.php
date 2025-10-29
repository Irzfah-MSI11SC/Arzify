@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">Detail Transaksi #{{ $trx->idtransaksi }}</h4>
  <a href="{{ route('transaksi.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div>

<div class="row g-3">
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        <div class="mb-2">
          <span class="text-secondary">Tanggal</span><br>
          {{ \Illuminate\Support\Carbon::parse($trx->tanggal)->format('d M Y H:i') }}
        </div>

        <div class="mb-2">
          <span class="text-secondary">Metode Bayar</span><br class="mb-1">
          <span class="badge bg-primary text-uppercase">{{ $trx->metode_bayar }}</span>
        </div>

        <div class="mb-0 d-flex justify-content-between align-items-center">
          <span class="h5 m-0">Total</span>
          <span class="h5 m-0">Rp {{ number_format($trx->total, 0, ',', '.') }}</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-8">
    <div class="card h-100">
      <div class="card-header">Item</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
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
                <tr>
                  <td>{{ optional($d->produk)->nama ?? '(produk diarsipkan/dihapus)' }}</td>
                  <td class="text-center">{{ $d->qty }}</td>
                  <td class="text-end">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                  <td class="text-end">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
