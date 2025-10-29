@extends('layouts.app')

@section('content')
<h4 class="mb-3">Transaksi Baru</h4>

<div class="row g-3">
  {{-- ========== KIRI: Daftar Produk ========== --}}
  <div class="col-12 col-lg-7">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Produk</span>
        <form method="get" class="d-flex" action="{{ route('transaksi.new') }}">
          <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm me-2" placeholder="Cari...">
          <button class="btn btn-sm btn-outline-cyan">Cari</button>
        </form>
      </div>

      <div class="card-body">
        @if(isset($produk) && $produk->count())
          <div class="row g-3">
            @foreach($produk as $p)
              <div class="col-6 col-md-4">
                <div class="card h-100">
                  @php $src = $p->gambar ? 'data:image/png;base64,'.base64_encode($p->gambar) : null; @endphp
                  @if($src)
                    <img src="{{ $src }}" class="card-img-top object-fit-cover" style="height:140px" alt="gambar {{ $p->nama }}">
                  @else
                    <div class="d-flex align-items-center justify-content-center text-secondary" style="height:140px;background:#0f141b">Tanpa Gambar</div>
                  @endif

                  <div class="card-body">
                    <div class="fw-semibold text-truncate mb-1" title="{{ $p->nama }}">{{ $p->nama }}</div>
                    <div class="small text-secondary mb-2">Rp {{ number_format($p->harga,0,',','.') }}</div>
                    <form method="post" action="{{ route('transaksi.addItem') }}">
                      @csrf
                      <input type="hidden" name="idproduk" value="{{ $p->idproduk }}">
                      <button class="btn btn-accent btn-sm w-100">
                        <i class="bi bi-cart-plus me-1"></i> Tambah
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-center text-secondary">Tidak ada produk.</div>
        @endif
      </div>

      {{-- Pagination (aman) --}}
      @if($produk instanceof \Illuminate\Contracts\Pagination\Paginator && $produk->hasPages())
        <div class="card-footer">
          {{ $produk->links() }}
        </div>
      @endif
    </div>
  </div>

  {{-- ========== KANAN: Keranjang ========== --}}
  <div class="col-12 col-lg-5">
    <div class="card h-100">
      <div class="card-header">Keranjang</div>

      <div class="card-body">
        @php
          $items = $items ?? session('cart', []);
          $grand = $total ?? collect($items)->sum('subtotal');
          // Cari file qris.png di public/image atau public/images
          $qrisAsset = null;
          if (file_exists(public_path('image/qris.png'))) {
            $qrisAsset = asset('image/qris.png');
          } elseif (file_exists(public_path('images/qris.png'))) {
            $qrisAsset = asset('images/qris.png');
          }
        @endphp

        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr class="text-secondary">
                <th>Item</th>
                <th style="width:230px">Qty</th>
                <th class="text-end" style="width:130px">Harga</th>
                <th class="text-end" style="width:130px">Sub</th>
                <th style="width:46px"></th>
              </tr>
            </thead>
            <tbody>
            @forelse($items as $row)
              @php
                $pid   = $row['idproduk'];
                $qty   = (int) $row['qty'];
                $harga = (float) $row['harga'];
                $sub   = (float) $row['subtotal'];
              @endphp
              <tr>
                <td class="fw-semibold">{{ $row['nama'] }}</td>

                <td>
                  <div class="d-flex align-items-center gap-2 flex-wrap">

                    {{-- minus --}}
                    <form method="post" action="{{ route('transaksi.updateQty') }}">
                      @csrf
                      <input type="hidden" name="idproduk" value="{{ $pid }}">
                      <input type="hidden" name="qty" value="{{ max(1, $qty-1) }}">
                      <button class="btn btn-outline-secondary btn-sm" title="Kurangi">
                        <i class="bi bi-dash"></i>
                      </button>
                    </form>

                    {{-- input + OK --}}
                    <form method="post" action="{{ route('transaksi.updateQty') }}" class="d-inline-flex align-items-center gap-2">
                      @csrf
                      <input type="hidden" name="idproduk" value="{{ $pid }}">
                      <input type="number"
                             name="qty"
                             min="1"
                             value="{{ $qty }}"
                             class="form-control form-control-sm text-center"
                             style="width:72px">
                      <button class="btn btn-outline-cyan btn-sm" title="Set Qty">OK</button>
                    </form>

                    {{-- plus --}}
                    <form method="post" action="{{ route('transaksi.updateQty') }}">
                      @csrf
                      <input type="hidden" name="idproduk" value="{{ $pid }}">
                      <input type="hidden" name="qty" value="{{ $qty+1 }}">
                      <button class="btn btn-outline-secondary btn-sm" title="Tambah">
                        <i class="bi bi-plus"></i>
                      </button>
                    </form>

                  </div>
                </td>

                <td class="text-end">Rp {{ number_format($harga,0,',','.') }}</td>
                <td class="text-end">Rp {{ number_format($sub,0,',','.') }}</td>

                <td class="text-end">
                  <form method="post" action="{{ route('transaksi.removeItem') }}" onsubmit="return confirm('Hapus item ini?')">
                    @csrf
                    <input type="hidden" name="idproduk" value="{{ $pid }}">
                    <button class="btn btn-outline-danger btn-sm" title="Hapus">
                      <i class="bi bi-x"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-secondary py-4">Keranjang kosong</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center">
          <div class="h5 m-0">Total</div>
          <div class="h5 m-0">Rp {{ number_format($grand,0,',','.') }}</div>
        </div>
        <input type="hidden" id="grandTotal" value="{{ (int)$grand }}">

        <hr class="my-3">

        {{-- ========== Pembayaran ========== --}}
        <form method="post" action="{{ route('transaksi.simpan') }}" id="payForm" novalidate>
          @csrf

          <div class="mb-3">
            <label class="form-label">Metode Bayar</label>
            <select name="metode_bayar" id="metodeBayar" class="form-select" required>
              <option value="">- pilih -</option>
              <option value="tunai">Tunai</option>
              <option value="qris">QRIS</option>
            </select>
          </div>

          {{-- TUNAI --}}
          <div id="tunaiBox" class="mb-3 d-none">
            <label class="form-label">Uang Tunai</label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" step="100" min="0" class="form-control" name="uang_tunai" id="uangTunai" placeholder="Masukkan jumlah uang">
            </div>
            <div class="form-text" id="kembalianText">Kembalian: Rp 0</div>
          </div>

          {{-- QRIS (gambar statis PNG) --}}
          <div id="qrisBox" class="mb-3 d-none">
            <label class="form-label">QRIS</label>
            <div class="p-2 bg-white d-inline-block rounded border">
              @if($qrisAsset)
                <img id="qrisImg" src="{{ $qrisAsset }}" alt="QRIS" width="220" height="220" style="image-rendering: pixelated">
              @else
                <div class="small text-secondary">Taruh file <code>qris.png</code> di <code>public/image/</code> atau <code>public/images/</code> untuk menampilkan QR.</div>
              @endif
            </div>
            <div class="small text-secondary mt-2">
              Nominal: <strong>Rp {{ number_format($grand, 0, ',', '.') }}</strong>
            </div>
          </div>

          <button class="btn btn-accent w-100">
            <i class="bi bi-check2-circle me-1"></i> Selesaikan Pembayaran
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
(function(){
  const metode   = document.getElementById('metodeBayar');
  const boxTunai = document.getElementById('tunaiBox');
  const boxQris  = document.getElementById('qrisBox');
  const uang     = document.getElementById('uangTunai');
  const total    = Number(document.getElementById('grandTotal').value || 0);
  const textKmb  = document.getElementById('kembalianText');
  const form     = document.getElementById('payForm');

  function rupiah(n){ return 'Rp ' + (Number(n)||0).toLocaleString('id-ID'); }
  function hitung(){
    const val = Number(uang.value || 0);
    textKmb.textContent = 'Kembalian: ' + rupiah(Math.max(val - total, 0));
  }

  function toggleBox(){
    const val = metode.value;
    if (val === 'tunai') {
      boxTunai.classList.remove('d-none');
      boxQris.classList.add('d-none');
      if (uang) {
        uang.setAttribute('required','required');
        uang.focus();
        hitung();
      }
    } else if (val === 'qris') {
      boxQris.classList.remove('d-none');
      boxTunai.classList.add('d-none');
      if (uang) {
        uang.removeAttribute('required');
        uang.value = '';
        textKmb.textContent = 'Kembalian: Rp 0';
      }
    } else {
      boxTunai.classList.add('d-none');
      boxQris.classList.add('d-none');
      if (uang) {
        uang.removeAttribute('required');
        uang.value = '';
        textKmb.textContent = 'Kembalian: Rp 0';
      }
    }
  }

  metode.addEventListener('change', toggleBox);
  uang && uang.addEventListener('input', hitung);

  // Validasi submit: kalau tunai, uang harus >= total
  form.addEventListener('submit', function(e){
    if (metode.value === 'tunai') {
      const val = Number(uang.value || 0);
      if (val < total) {
        e.preventDefault();
        alert('Uang tunai kurang dari total pembayaran (' + rupiah(total) + ').');
        uang && uang.focus();
      }
    }
  });

  // Inisialisasi awal
  toggleBox();
})();
</script>
@endsection
