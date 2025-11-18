{{-- resources/views/transaksi/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-md-row gap-2 mb-3 justify-content-between align-items-md-center">
  <h4 class="m-0">Riwayat Transaksi</h4>
</div>

@if(isset($data) && $data->count())
  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="text-secondary">
            <tr>
              <th style="width:70px">#ID</th>
              <th style="width:180px">Tanggal</th>
              <th>Kasir</th>
              <th style="width:130px">Metode</th>
              <th class="text-end" style="width:160px">Total</th>
              <th class="text-end" style="width:110px">Aksi</th>
            </tr>
          </thead>
          <tbody>
          @foreach($data as $trx)
            <tr>
              <td class="fw-semibold">#{{ $trx->idtransaksi }}</td>

              <td>
                {{ \Illuminate\Support\Carbon::parse($trx->tanggal)->format('d M Y H:i') }}
              </td>

              <td>
                {{-- kalau relasi kasir ada, tampilkan namanya; fallback ke "-" --}}
                {{ $trx->kasir->nama ?? $trx->kasir->username ?? '-' ?? '-' }}
              </td>

              <td class="text-uppercase">
                <span class="badge bg-primary-subtle text-primary">
                  {{ $trx->metode_bayar }}
                </span>
              </td>

              <td class="text-end">
                Rp {{ number_format($trx->total, 0, ',', '.') }}
              </td>

              <td class="text-end">
                <a href="{{ route('transaksi.show', $trx->idtransaksi) }}"
                   class="btn btn-sm btn-outline-cyan">
                  <i class="bi bi-receipt me-1"></i> Detail
                </a>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="small text-secondary">
          Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} transaksi
        </div>
        <div>
          {{ $data->links() }}
        </div>
      </div>
    </div>
  </div>
@else
  <div class="card card-body text-center text-secondary">
    Belum ada transaksi.
  </div>
@endif
@endsection
