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

        // Hanya tampilkan produk yang masih punya stok (> 0)
        $produk = Produk::query()
            ->when($q !== '', fn ($qq) => $qq->where('nama', 'like', "%{$q}%"))
            ->where('stok', '>', 0)
            ->orderBy('nama')
            ->paginate(12)
            ->withQueryString();

        // Bersihkan keranjang dari item produk yang sudah tidak ada di DB
        $cart = collect(session('cart', []))
            ->filter(fn ($row) => Produk::where('idproduk', $row['idproduk'])->exists())
            ->all();
        session(['cart' => $cart]);

        // Ambil transaksi terakhir (jika ada) untuk banner cepat
        $lastTrx = null;
        if (session()->has('last_trx_id')) {
            $lastTrx = Transaksi::with(['details.produk', 'kasir'])
                ->find(session('last_trx_id'));
        }

        return view('transaksi.new', [
            'title'  => 'Transaksi Baru',
            'produk' => $produk,
            'q'      => $q,
            'lastTrx'=> $lastTrx,
        ]);
    }

    /**
     * Tambah item ke keranjang (session)
     * - Dibatasi oleh stok terkini (stok decimal → qty integer memakai floor(stok))
     */
    public function addItem(Request $r)
    {
        $r->validate([
            'idproduk' => 'required|integer|exists:produk,idproduk',
        ]);

        $finder = method_exists(Produk::class, 'scopeAktif') ? Produk::aktif() : Produk::query();
        $p = $finder->findOrFail($r->idproduk);

        $stokNow = (float) $p->stok;
        $maxQty  = (int) floor($stokNow);
        if ($maxQty < 1) {
            return back()->with('error', 'Stok produk habis. Mohon isi stok terlebih dahulu.');
        }

        $cart = session('cart', []);
        $key  = (string) $p->idproduk;
        $inCart = isset($cart[$key]) ? (int)$cart[$key]['qty'] : 0;

        if ($inCart + 1 > $maxQty) {
            return back()->with('error', 'Stok kurang. Stok tersisa: ' . max(0, $maxQty - $inCart));
        }

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
            $cart[$key]['qty']      = $inCart + 1;
            $cart[$key]['subtotal'] = $cart[$key]['qty'] * $cart[$key]['harga'];
        }

        session(['cart' => $cart]);
        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    /**
     * Update qty item di keranjang
     * - Batasi qty <= floor(stok sekarang)
     * - Jika stok 0, item dihapus dari keranjang
     */
    public function updateQty(Request $r)
    {
        $r->validate([
            'idproduk' => 'required|integer',
            'qty'      => 'required|integer|min:1',
        ]);

        $cart = session('cart', []);
        $key  = (string) $r->idproduk;
        if (!isset($cart[$key])) return back();

        $p = Produk::find($r->idproduk);
        if (!$p) {
            unset($cart[$key]);
            session(['cart' => $cart]);
            return back()->with('error', 'Produk tidak tersedia.');
        }

        $stokNow = (float) $p->stok;
        $maxQty  = (int) floor($stokNow);

        if ($maxQty < 1) {
            unset($cart[$key]);
            session(['cart' => $cart]);
            return back()->with('error', 'Stok habis. Item dihapus dari keranjang.');
        }

        $reqQty = (int) $r->qty;
        $msg    = null;
        if ($reqQty > $maxQty) {
            $reqQty = $maxQty;
            $msg = 'Stok kurang. Qty diset ke ' . $maxQty . '.';
        }

        $cart[$key]['qty']      = $reqQty;
        $cart[$key]['subtotal'] = $reqQty * $cart[$key]['harga'];
        session(['cart' => $cart]);

        return $msg ? back()->with('error', $msg) : back();
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
     * - Kurangi stok produk (aman: lock row + WHERE stok>=qty)
     * - Kosongkan keranjang
     * - Simpan last_trx_id untuk banner di halaman new()
     */
    public function simpan(Request $r)
    {
        $r->validate([
            'metode_bayar' => 'required|in:tunai,qris',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Keranjang masih kosong.');
        }

        foreach ($cart as $row) {
            if (!Produk::where('idproduk', $row['idproduk'])->exists()) {
                return back()->with('error', 'Ada produk yang sudah dihapus dari katalog. Mohon cek keranjang.');
            }
        }

        $total = collect($cart)->sum('subtotal');

        if ($r->metode_bayar === 'tunai') {
            $uangTunai = (float) ($r->uang_tunai ?? 0);
            if ($uangTunai < $total) {
                return back()->with('error', 'Uang tunai kurang dari total pembayaran.');
            }
        }

        $kasirId = session('kasir_id');
        if (!$kasirId) {
            return back()->with('error', 'Sesi kasir tidak ditemukan.');
        }

        try {
            $trxId = DB::transaction(function () use ($cart, $kasirId, $total, $r) {
                // Lock baris produk
                $ids = collect($cart)->pluck('idproduk')->values();
                $produkMap = Produk::whereIn('idproduk', $ids)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('idproduk');

                foreach ($cart as $row) {
                    $p = $produkMap[$row['idproduk']] ?? null;
                    if (!$p) throw new \RuntimeException('Produk hilang saat menyimpan transaksi.');
                    $stokTersedia = (float) $p->stok;
                    $butuh        = (int) $row['qty'];
                    if ($stokTersedia < $butuh) {
                        throw new \RuntimeException("Stok {$p->nama} berubah/habis. Sisa: {$stokTersedia}");
                    }
                }

                // Simpan transaksi
                $trx = Transaksi::create([
                    'idkasir'      => $kasirId,
                    'tanggal'      => Carbon::now(),
                    'total'        => (float) $total,
                    'metode_bayar' => $r->metode_bayar,
                ]);

                // Detail + kurangi stok
                foreach ($cart as $row) {
                    DetailTransaksi::create([
                        'idtransaksi'  => $trx->idtransaksi,
                        'idproduk'     => $row['idproduk'],
                        'qty'          => (int) $row['qty'],
                        'harga_satuan' => (float) $row['harga'],
                        'subtotal'     => (float) $row['subtotal'],
                        'satuan_jual'  => $row['satuan'] ?? 'pcs',
                    ]);

                    $affected = DB::table('produk')
                        ->where('idproduk', $row['idproduk'])
                        ->where('stok', '>=', (float)$row['qty'])
                        ->update([
                            'stok' => DB::raw('stok - ' . ((float)$row['qty']))
                        ]);

                    if ($affected === 0) {
                        throw new \RuntimeException('Stok berubah saat penyimpanan. Silakan ulangi transaksi.');
                    }
                }

                // kosongkan keranjang
                session()->forget('cart');

                return $trx->idtransaksi;
            });

            // simpan id transaksi terakhir untuk banner
            session(['last_trx_id' => $trxId]);

            if ($r->metode_bayar === 'tunai') {
                $kembalian = (float) ($r->uang_tunai ?? 0) - (float) $total;
                return redirect()
                    ->route('transaksi.new')
                    ->with('success', 'Transaksi disimpan. Kembalian: Rp ' . number_format(max($kembalian, 0), 0, ',', '.'));
            }

            return redirect()->route('transaksi.new')->with('success', 'Transaksi disimpan.');
        } catch (\Throwable $e) {
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
        $trx = Transaksi::with(['details.produk', 'kasir'])->findOrFail($id);
        $totalItems = $trx->details->sum('qty');

        return view('transaksi.show', [
            'title'      => 'Detail Transaksi',
            'trx'        => $trx,
            'totalItems' => $totalItems,
        ]);
    }
}
