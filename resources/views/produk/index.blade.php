@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-md-row gap-2 mb-3 justify-content-between align-items-md-center">
  <h4 class="m-0">Produk</h4>

  <div class="d-flex gap-2">
    <form class="d-flex" method="get" action="{{ route('produk.index') }}">
      <input name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari nama...">
      <button class="btn btn-outline-cyan ms-2">Cari</button>
    </form>
    <a href="{{ route('produk.create') }}" class="btn btn-accent">Tambah</a>
  </div>
</div>

{{-- Grid produk --}}
@if(isset($produk) && $produk->count())
  <div class="row g-3">
    @foreach($produk as $p)
      <div class="col-6 col-md-4 col-lg-3">
        <div class="card h-100 product-card card-hover">

          {{-- Gambar --}}
          @php
            $src = $p->gambar ? 'data:image/png;base64,'.base64_encode($p->gambar) : null;
          @endphp
          @if($src)
            <img class="card-img-top object-fit-cover" style="height:150px" src="{{ $src }}" alt="Gambar {{ $p->nama }}">
          @else
            <div class="d-flex align-items-center justify-content-center text-secondary"
                 style="height:150px;background:#0f141b">
              Tanpa Gambar
            </div>
          @endif

          {{-- Body --}}
          <div class="card-body d-flex flex-column">
            <div class="fw-semibold mb-1 text-truncate" title="{{ $p->nama }}">{{ $p->nama }}</div>

            {{-- Kategori • Satuan --}}
            <div class="text-secondary small mb-2">
              {{ $p->kategori->nama ?? '-' }} • {{ $p->satuan_base ?: 'pcs' }}
            </div>

            <div class="mt-auto d-flex justify-content-between small">
              <span>Stok: {{ rtrim(rtrim(number_format($p->stok, 2, ',', '.'), '0'), ',') }}</span>
              <span>Rp {{ number_format($p->harga,0,',','.') }}</span>
            </div>
          </div>

          {{-- Footer: Ubah / Hapus / Tambah Stok --}}
          <div class="card-footer">
            <div class="d-flex flex-wrap gap-2 mb-2">
              <a href="{{ route('produk.edit', $p->idproduk) }}"
                 class="btn btn-sm btn-outline-cyan flex-fill">
                 Ubah
              </a>

              {{-- Hapus pakai modal custom --}}
              <form method="post"
                    action="{{ route('produk.destroy', $p->idproduk) }}"
                    class="flex-fill form-delete-produk"
                    data-nama="{{ $p->nama }}">
                @csrf
                @method('delete')
                <button type="submit" class="btn btn-sm btn-outline-danger w-100 btn-delete-produk">
                  Hapus
                </button>
              </form>
            </div>

            {{-- Tombol Tambah Stok --}}
            <button type="button"
                    class="btn btn-sm btn-outline-success w-100 btn-add-stock"
                    data-id="{{ $p->idproduk }}"
                    data-nama="{{ $p->nama }}">
              + Tambah Stok
            </button>
          </div>

        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-3">
    {{ $produk->withQueryString()->links() }}
  </div>
@else
  <div class="card card-body text-center text-secondary">Tidak ada produk.</div>
@endif

{{-- ================= MODAL HAPUS PRODUK ================= --}}
<div class="modal fade" id="deleteProdukModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-surface text-base border border-secondary">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus Produk</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <p id="deleteProdukText" class="mb-0"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
          Tidak
        </button>
        <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteProduk">
          Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>
{{-- ====================================================== --}}

{{-- ================= MODAL TAMBAH STOK ================== --}}
<div class="modal fade" id="addStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-surface text-base border border-secondary">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Stok Produk</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      {{-- action di-set via JS (supaya bisa kirim /produk/{id}/tambah-stok) --}}
      <form method="post" action="" id="addStockForm">
        @csrf
        <div class="modal-body">
          <input type="hidden" name="idproduk" id="stokProdukId">

          <div class="mb-3">
            <label class="form-label">Produk</label>
            <input type="text" id="stokProdukNama" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label">Jumlah Stok yang Ditambahkan</label>
            {{-- samakan dengan controller: name="qty" --}}
            <input type="number" name="qty" id="stokJumlah" class="form-control" min="1" value="1" required>
            <div class="form-text">Masukkan angka stok yang ingin ditambahkan.</div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
            Batal
          </button>
          <button type="submit" class="btn btn-success btn-sm">
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
{{-- ====================================================== --}}
@endsection

@section('scripts')
<script>
(function () {
  /* ========= MODAL HAPUS PRODUK ========= */
  let deleteForm = null;
  const modalEl   = document.getElementById('deleteProdukModal');
  const textEl    = document.getElementById('deleteProdukText');
  const btnOk     = document.getElementById('confirmDeleteProduk');

  if (modalEl && textEl && btnOk) {
    const modal = new bootstrap.Modal(modalEl, {
      backdrop: 'static',
      keyboard: false
    });

    document.querySelectorAll('.form-delete-produk').forEach(form => {
      form.addEventListener('submit', function (e) {
        e.preventDefault();

        deleteForm = this;
        const nama = this.getAttribute('data-nama') || 'produk ini';

        textEl.textContent =
          'Hapus produk ' + nama +
          '? Produk yang sudah dipakai di transaksi tidak bisa dihapus.';

        modal.show();
      });
    });

    btnOk.addEventListener('click', function () {
      if (deleteForm) {
        modal.hide();
        deleteForm.submit();
      }
    });
  }

  /* ========= MODAL TAMBAH STOK ========= */
  const addStockModalEl = document.getElementById('addStockModal');
  const stokProdukId    = document.getElementById('stokProdukId');
  const stokProdukNama  = document.getElementById('stokProdukNama');
  const stokJumlah      = document.getElementById('stokJumlah');
  const addStockForm    = document.getElementById('addStockForm');

  if (addStockModalEl && stokProdukId && stokProdukNama && stokJumlah && addStockForm) {
    const stockModal = new bootstrap.Modal(addStockModalEl, {
      backdrop: 'static',
      keyboard: false
    });

    const baseUrl = "{{ url('produk') }}"; // -> /produk

    document.querySelectorAll('.btn-add-stock').forEach(btn => {
      btn.addEventListener('click', function () {
        const id   = this.getAttribute('data-id');
        const nama = this.getAttribute('data-nama') || '';

        stokProdukId.value   = id || '';
        stokProdukNama.value = nama;
        stokJumlah.value     = 1;

        // set action: /produk/{id}/tambah-stok
        addStockForm.action = baseUrl + '/' + id + '/tambah-stok';

        stockModal.show();
      });
    });
  }
})();
</script>
@endsection
