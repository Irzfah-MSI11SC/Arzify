@extends('layouts.app')

@section('content')
<div class="d-flex mb-3 justify-content-between align-items-center">
  <h4 class="m-0">Kategori</h4>
  <a href="{{ route('kategori.create') }}" class="btn btn-accent">Tambah</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle m-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @foreach($kategori as $k)
          <tr>
            <td>{{ $k->idkategori }}</td>
            <td>{{ $k->nama }}</td>
            <td class="text-end">
              <a href="{{ route('kategori.edit',$k) }}" class="btn btn-sm btn-outline-cyan">Ubah</a>

              {{-- FORM HAPUS: TANPA confirm() – pakai modal --}}
              <form action="{{ route('kategori.destroy',$k) }}"
                    method="post"
                    class="d-inline form-delete"
                    data-entity="kategori"
                    data-name="{{ $k->nama }}">
                @csrf
                @method('delete')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  Hapus
                </button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">{{ $kategori->withQueryString()->links() }}</div>

{{-- MODAL KONFIRMASI HAPUS (boleh sama ID, karena halaman berbeda) --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-surface text-base border border-secondary">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div id="confirmDeleteMessage">
          Apakah Anda yakin ingin menghapus data ini?
        </div>
      </div>
      <div class="modal-footer">
        <button type="button"
                class="btn btn-outline-secondary btn-sm"
                data-bs-dismiss="modal">
          Tidak
        </button>
        <button type="button"
                class="btn btn-danger btn-sm"
                id="confirmDeleteBtn">
          Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  (function () {
    let formToSubmit = null;

    const modalEl   = document.getElementById('confirmDeleteModal');
    const messageEl = document.getElementById('confirmDeleteMessage');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    if (!modalEl || !messageEl || !confirmBtn) return;

    const bsModal = new bootstrap.Modal(modalEl, {
      backdrop: 'static',
      keyboard: false
    });

    document.querySelectorAll('form.form-delete').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();

        const entity = form.getAttribute('data-entity') || 'data';
        const name   = form.getAttribute('data-name')   || '';

        messageEl.textContent = `Hapus ${entity} "${name}"?`;
        formToSubmit = form;

        bsModal.show();
      });
    });

    confirmBtn.addEventListener('click', function () {
      if (formToSubmit) {
        formToSubmit.submit();
        formToSubmit = null;
      }
    });
  })();
</script>
@endsection
