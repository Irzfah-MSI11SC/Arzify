@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">Riwayat Transaksi</h4>
  <a href="{{ route('transaksi.new') }}" class="btn btn-accent">
    <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
  </a>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="text-secondary">
          <tr>
            <th style="width: 120px">Tanggal</th>
            <th class="text-center" style="width: 110px">Items</th>
            <th style="width: 140px">Metode</th>
            <th class="text-end" style="width: 160px">Total</th>
            <th style="width: 90px"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($data as $t)
            <tr>
              <td>{{ $t->tanggal?->format('d M Y H:i') }}</td>
              <td class="text-center">{{ $t->details_count }}</td>
              <td class="text-uppercase">{{ $t->metode_bayar }}</td>
              <td class="text-end">Rp {{ number_format($t->total,0,',','.') }}</td>
              <td class="text-end">
                <a href="{{ route('transaksi.show',$t->idtransaksi) }}" class="btn btn-sm btn-outline-cyan">
                  Detail
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-secondary">Belum ada transaksi.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @if($data instanceof \Illuminate\Contracts\Pagination\Paginator && $data->hasPages())
    <div class="card-footer">{{ $data->links() }}</div>
  @endif
</div>
@endsection
