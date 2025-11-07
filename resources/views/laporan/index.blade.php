@extends('layouts.app')

@section('content')
<h4 class="mb-3">Laporan Penjualan</h4>

<form class="card card-body mb-3 auto-contrast" method="get" action="{{ route('laporan.index') }}">
  <div class="row g-2 align-items-end">
    <div class="col-12 col-md-4">
      <label class="form-label">Dari</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
        <input type="date" name="start" value="{{ $start }}" class="form-control">
      </div>
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">Sampai</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
        <input type="date" name="end" value="{{ $end }}" class="form-control">
      </div>
    </div>
    <div class="col-12 col-md-3">
      <label class="form-label">Tipe Periode</label>
      @php $tp = $tipe_periode ?? request('tipe_periode', 'Harian'); @endphp
      <select name="tipe_periode" class="form-select">
        <option {{ $tp=='Harian'   ? 'selected' : '' }}>Harian</option>
        <option {{ $tp=='Mingguan' ? 'selected' : '' }}>Mingguan</option>
        <option {{ $tp=='Bulanan'  ? 'selected' : '' }}>Bulanan</option>
        <option {{ $tp=='Tahunan'  ? 'selected' : '' }}>Tahunan</option>
      </select>
    </div>
    <div class="col-12 col-md-1">
      <button class="btn btn-accent w-100">Tampilkan</button>
    </div>
  </div>
</form>

<div class="row g-3 mb-3">
  <div class="col-12 col-lg-4">
    <div class="card stat-card"><div class="card-body">
      <div class="text-muted-2 small">Total Transaksi</div>
      <div class="display-6">{{ $totalTrx }}</div>
    </div></div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card stat-card"><div class="card-body">
      <div class="text-muted-2 small">Total Pendapatan</div>
      <div class="display-6">Rp {{ number_format($pendapatan,0,',','.') }}</div>
    </div></div>
  </div>
  <div class="col-12 col-lg-4">
    <div class="card stat-card"><div class="card-body">
      <div class="text-muted-2 small">Item Terjual</div>
      <div class="display-6">{{ $totalItems }}</div>
    </div></div>
  </div>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Tren Periode</span>
        <div class="small text-muted-2">Omzet (Rp), Transaksi, Item</div>
      </div>
      <div class="card-body"><div style="height:360px">
        <canvas id="trendChart"></canvas>
      </div></div>
    </div>
  </div>
  <div class="col-12 col-xl-4">
    <div class="card h-100">
      <div class="card-header">Metode Pembayaran</div>
      <div class="card-body">
        <div style="max-width:420px; margin-inline:auto;">
          <canvas id="payChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- data untuk chart --}}
<div id="laporan-data"
     data-labels='@json($labels)'
     data-omzet='@json($seriesOmzet)'
     data-trx='@json($seriesTrx)'
     data-items='@json($seriesItems)'
     data-paylab='@json($donutLabels)'
     data-paydata='@json($donutData)'></div>
@endsection

@section('scripts')
<script>
  const el      = document.getElementById('laporan-data');
  const LABELS  = JSON.parse(el.dataset.labels || '[]');
  const OMZET   = JSON.parse(el.dataset.omzet  || '[]');
  const TRX     = JSON.parse(el.dataset.trx    || '[]');
  const ITEMS   = JSON.parse(el.dataset.items  || '[]');
  const PAYLAB  = JSON.parse(el.dataset.paylab || '[]');
  const PAYDATA = JSON.parse(el.dataset.paydata|| '[]');

  const rupiah = (n) => 'Rp ' + (n || 0).toLocaleString('id-ID');

  const cOmzet = 'rgba(0, 160, 255, .88)';   // biru
  const cTrx   = 'rgba(255, 99, 132, .85)';  // merah
  const cItem  = 'rgba(255, 171, 0, .85)';   // kuning
  const gridC  = 'rgba(255,255,255,.06)';

  // Semua batang (bar) agar konsisten
  new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: LABELS,
      datasets: [
        { type:'bar', label:'Omzet (Rp)', data:OMZET, backgroundColor:cOmzet, borderRadius:6, yAxisID:'yRp',   order:1, barPercentage:.6, categoryPercentage:.6 },
        { type:'bar', label:'Transaksi',  data:TRX,   backgroundColor:cTrx,  borderRadius:6, yAxisID:'yCnt',  order:2, barPercentage:.6, categoryPercentage:.6 },
        { type:'bar', label:'Item',       data:ITEMS, backgroundColor:cItem, borderRadius:6, yAxisID:'yCnt',  order:2, barPercentage:.6, categoryPercentage:.6 },
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false, interaction:{ mode:'index', intersect:false },
      scales:{
        x:{ grid:{ color:gridC }, ticks:{ font:{ size:12 }}},
        yRp:{
          type:'linear', position:'left', grid:{ color:gridC },
          ticks:{ callback:(v)=>rupiah(v), font:{ size:12 }},
          title:{ display:true, text:'Omzet (Rp)' }
        },
        yCnt:{
          type:'linear', position:'right', grid:{ display:false },
          ticks:{ precision:0, font:{ size:12 }},
          title:{ display:true, text:'Jumlah' }
        }
      },
      plugins:{
        legend:{ labels:{ usePointStyle:true, boxWidth:8 }},
        tooltip:{ callbacks:{ label:(ctx)=>{
          const name = ctx.dataset.label || '';
          const v = ctx.parsed.y;
          return name.includes('Omzet') ? `${name}: ${rupiah(v)}` : `${name}: ${v}`;
        }}}
      }
    }
  });

  // Donut metode bayar
  new Chart(document.getElementById('payChart').getContext('2d'), {
    type:'doughnut',
    data:{
      labels: PAYLAB.map(s=>s.toUpperCase()),
      datasets:[{ data:PAYDATA, backgroundColor:['#2ecc71','#00bcd4','#f39c12','#9b59b6','#e74c3c'], borderWidth:0 }]
    },
    options:{
      cutout:'70%',
      plugins:{
        legend:{ labels:{ usePointStyle:true, boxWidth:8 }},
        tooltip:{ callbacks:{ label:(ctx)=> `${ctx.label}: ${rupiah(ctx.parsed)}` } }
      }
    }
  });
</script>
@endsection
