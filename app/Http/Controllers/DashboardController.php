<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Kartu ringkas (atas)
        $produkCount = Produk::count();
        $trxToday    = Transaksi::whereDate('tanggal', $today)->count();
        $omzetToday  = (float) Transaksi::whereDate('tanggal', $today)->sum('total');
        $itemsToday  = (int) DetailTransaksi::whereHas('transaksi', function ($q) use ($today) {
                            $q->whereDate('tanggal', $today);
                        })->sum('qty');

        // Seri 7 hari terakhir (termasuk hari ini)
        $days   = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i));
        $labels = $days->map(fn($d) => $d->format('d M'));

        $omzet = $days->map(fn($d) => (float) Transaksi::whereDate('tanggal', $d)->sum('total'));
        $trx   = $days->map(fn($d) => (int)   Transaksi::whereDate('tanggal', $d)->count());
        $items = $days->map(fn($d) => (int)   DetailTransaksi::whereHas('transaksi', fn($q) => $q->whereDate('tanggal', $d))
                                                            ->sum('qty'));

        return view('dashboard.index', compact(
            'produkCount',
            'trxToday',
            'omzetToday',
            'itemsToday',
            'labels',
            'omzet',
            'trx',
            'items'
        ));
    }
}
