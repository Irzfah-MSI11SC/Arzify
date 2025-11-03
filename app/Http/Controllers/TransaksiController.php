<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;

class TransaksiController extends Controller
{
    /**
     * Halaman transaksi baru (daftar produk kiri + keranjang kanan)
     */
    public function new(Request $r)
    {
        $q = trim((string) $r->q);

        // Ambil daftar produk
        $produk = Produk::query()
            ->when($q !== '', fn($qq) => $qq->where('nama', 'like', "%{$q}%"))
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString();

        // Bersihkan keranjang dari item produk yang sudah tidak ada di DB
        $cart = collect(session('cart', []))
            ->filter(fn ($row) => Produk::where('idproduk', $row['idproduk'])->exists())
            ->all();

        session(['cart' => $cart]);

        return view('transaksi.new', [
            'title'  => 'Transaksi Baru',
            'produk' => $produk,
            'q'      => $q,
            // keranjang diambil langsung di blade dari session, jadi aman
        ]);
    }

    /**
     * Tambah item ke keranjang (session)
     */
    public function addItem(Request $r)
    {
        $r->validate([
            'idproduk' => 'required|integer|exists:produk,idproduk',
        ]);

        // kalau kamu punya scopeAktif() di model Produk, kita pakai
        $finder = method_exists(Produk::class, 'scopeAktif')
            ? Produk::aktif()
            : Produk::query();

        $p = $finder->findOrFail($r->idproduk);

        $cart = session('cart', []);
        $key  = (string) $p->idproduk;

        if (!isset($cart[$key])) {
            $cart[$key] = [
                'idproduk' => $p->idproduk,
                'nama'     => $p->nama,
                'harga'    => (float) $p->harga,
                'qty'      => 1,
                'subtotal' => (float) $p->harga,
                'satuan'   => $p->satuan_base ?: 'pcs',
            ];
        } else {
            $cart[$key]['qty']      += 1;
            $cart[$key]['subtotal']  = $cart[$key]['qty'] * $cart[$key]['harga'];
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    /**
     * Update qty item di keranjang
     */
    public function updateQty(Request $r)
    {
        $r->validate([
            'idproduk' => 'required|integer',
            'qty'      => 'required|integer|min:1',
        ]);

        $cart = session('cart', []);
        $key  = (string) $r->idproduk;

        if (isset($cart[$key])) {
            $cart[$key]['qty']      = (int) $r->qty;
            $cart[$key]['subtotal'] = $cart[$key]['qty'] * $cart[$key]['harga'];
            session(['cart' => $cart]);
        }

        return back();
    }

    /**
     * Hapus item dari keranjang
     */
    public function removeItem(Request $r)
    {
        $r->validate([
            'idproduk' => 'required|integer',
        ]);

        $cart = session('cart', []);
        $key  = (string) $r->idproduk;

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session(['cart' => $cart]);
        }

        return back()->with('success', 'Item dihapus.');
    }

    /**
     * Simpan transaksi (transaksi + detailtransaksi)
     * - Validasi uang tunai kalau metode = tunai
     * - Kurangi stok produk
     * - Kosongkan keranjang
     */
    public function simpan(Request $r)
    {
        $r->validate([
            'metode_bayar' => 'required|in:tunai,qris',
            // uang_tunai akan dicek manual setelah kita tahu total
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Keranjang masih kosong.');
        }

        // Pastikan semua produk di keranjang masih ada
        foreach ($cart as $row) {
            if (!Produk::where('idproduk', $row['idproduk'])->exists()) {
                return back()->with('error', 'Ada produk yang sudah dihapus dari katalog. Mohon cek keranjang.');
            }
        }

        $total = collect($cart)->sum('subtotal');

        // Validasi uang tunai (jika bayar tunai)
        if ($r->metode_bayar === 'tunai') {
            $uangTunai = (float) ($r->uang_tunai ?? 0);
            if ($uangTunai < $total) {
                return back()->with('error', 'Uang tunai kurang dari total pembayaran.');
            }
        }

        $kasirId = session('kasir_id'); // dari login
        if (!$kasirId) {
            return back()->with('error', 'Sesi kasir tidak ditemukan.');
        }

        DB::beginTransaction();
        try {
            // Simpan transaksi induk
            $trx = Transaksi::create([
                'idkasir'      => $kasirId,
                'tanggal'      => Carbon::now(),
                'total'        => (float) $total,
                'metode_bayar' => $r->metode_bayar,
                // Kalau tabel transaksi punya kolom untuk bayar/kembalian, tambahkan di sini:
                // 'bayar'       => $r->metode_bayar === 'tunai' ? (float) $r->uang_tunai : 0,
                // 'kembalian'   => $r->metode_bayar === 'tunai' ? ((float)$r->uang_tunai - $total) : 0,
            ]);

            // Simpan detail per item
            foreach ($cart as $row) {
                DetailTransaksi::create([
                    'idtransaksi'  => $trx->idtransaksi,
                    'idproduk'     => $row['idproduk'],
                    'qty'          => (int) $row['qty'],
                    'harga_satuan' => (float) $row['harga'],
                    'subtotal'     => (float) $row['subtotal'],
                    'satuan_jual'  => $row['satuan'] ?? 'pcs',
                ]);

                // Update stok
                Produk::where('idproduk', $row['idproduk'])
                    ->decrement('stok', (int) $row['qty']);
            }

            DB::commit();
            session()->forget('cart');

            // kalau tunai, kirim juga info kembalian ke flash message
            if ($r->metode_bayar === 'tunai') {
                $kembalian = (float) ($r->uang_tunai ?? 0) - $total;

                return redirect()
                    ->route('transaksi.new')
                    ->with('success', 'Transaksi disimpan. Kembalian: Rp ' . number_format(max($kembalian, 0), 0, ',', '.'));
            }

            return redirect()
                ->route('transaksi.new')
                ->with('success', 'Transaksi disimpan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Riwayat transaksi (list transaksi)
     */
    public function index()
    {
        $data = Transaksi::orderByDesc('tanggal')->paginate(20);

        return view('transaksi.index', [
            'title' => 'Riwayat Transaksi',
            'data'  => $data,
        ]);
    }

    /**
     * Detail transaksi (nota + tombol cetak)
     */
    public function show($id)
    {
        // ambil transaksi + semua detail + produk tiap detail
        $trx = Transaksi::with(['details.produk', 'kasir'])->findOrFail($id);

        return view('transaksi.show', [
            'title' => 'Detail Transaksi',
            'trx'   => $trx,
        ]);
    }
}
