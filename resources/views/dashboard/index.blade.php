@extends('layouts.app')
@section('content')
<h4 class="mb-3">Dashboard</h4>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="card stat-card card-hover"><div class="card-body">
      <div class="text-muted-2 small">Produk</div>
      <div class="h3 m-0">{{ $produkCount }}</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card card-hover"><div class="card-body">
      <div class="text-muted-2 small">Transaksi (Hari ini)</div>
      <div class="h3 m-0">{{ $trxToday }}</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card card-hover"><div class="card-body">
      <div class="text-muted-2 small">Pendapatan (Hari ini)</div>
      <div class="h3 m-0">Rp {{ number_format($omzetToday,0,',','.') }}</div>
    </div></div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card card-hover"><div class="card-body">
      <div class="text-muted-2 small">Item Terjual (Hari ini)</div>
      <div class="h3 m-0">{{ $itemsToday }}</div>
    </div></div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="fw-semibold">7 Hari Terakhir</div>
      <span class="chip">Omzet, Transaksi, Item</span>
    </div>
    <canvas id="chart7" height="80"></canvas>
  </div>
</div>

{{-- kirim data via JSON supaya editor tidak error --}}
<script type="application/json" id="dash-data">
{!! json_encode([
  'labels' => $labels,
  'omzet'  => $omzet,
  'trx'    => $trx,
  'items'  => $items,
], JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('scripts')
<script>
const d = JSON.parse(document.getElementById('dash-data').textContent);
new Chart(document.getElementById('chart7').getContext('2d'), {
  data: {
    labels: d.labels,
    datasets: [
      { type: 'line', label: 'Omzet (Rp)', data: d.omzet, borderWidth: 2, tension:.3, yAxisID:'yRp' },
      { type: 'bar',  label: 'Transaksi',  data: d.trx,   borderWidth: 1,         yAxisID:'yCnt' },
      { type: 'bar',  label: 'Item',       data: d.items, borderWidth: 1,         yAxisID:'yCnt' },
    ]
  },
  options: {
    responsive: true,
    scales: {
      yRp:  { type:'linear', position:'left',  grid:{drawOnChartArea:false} },
      yCnt: { type:'linear', position:'right' }
    },
    plugins:{ legend:{labels:{color:'#cfd6df'}} }
  }
});
</script>
@endsection
