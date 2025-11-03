@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <h4 class="m-0">Riwayat Transaksi</h4>
</div>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="text-secondary">
          <tr>
            <th style="width: 160px">Tanggal</th>
            <th class="text-center" style="width: 110px">Items</th>
            <th style="width: 140px">Metode</th>
            <th class="text-end" style="width: 160px">Total</th>
            <th style="width: 100px"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($data as $t)
          <tr>
            {{-- Kolom Tanggal --}}
            <td>
              {{-- karena $t->tanggal adalah string, kita format manual pakai date() --}}
              {{ date('d M Y H:i', strtotime($t->tanggal)) }}
            </td>

            {{-- Jumlah item --}}
            <td class="text-center">
              {{ $t->details_count }}
            </td>

            {{-- Metode bayar --}}
            <td class="text-uppercase">
              {{ $t->metode_bayar }}
            </td>

            {{-- Total --}}
            <td class="text-end">
              Rp {{ number_format($t->total, 0, ',', '.') }}
            </td>

            {{-- Tombol Aksi Detail --}}
            <td class="text-end">
              <a href="{{ route('transaksi.show', $t->idtransaksi) }}"
                 class="btn btn-sm btn-outline-cyan">
                Detail
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-secondary py-4">
              Belum ada transaksi.
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @if($data instanceof \Illuminate\Contracts\Pagination\Paginator && $data->hasPages())
    <div class="card-footer">
      {{ $data->links() }}
    </div>
  @endif
</div>
@endsection
