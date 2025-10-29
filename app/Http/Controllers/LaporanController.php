<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;

class LaporanController extends Controller
{
    public function index(Request $r)
    {
        $start = $r->get('start') ?: Carbon::today()->format('Y-m-d');
        $end   = $r->get('end')   ?: Carbon::today()->format('Y-m-d');
        $tipe  = $r->get('tipe')  ?: 'Harian';

        $startDt = Carbon::parse($start)->startOfDay();
        $endDt   = Carbon::parse($end)->endOfDay();

        // Ringkasan
        $ringkasan = DB::table('transaksi')
            ->whereBetween('tanggal', [$startDt, $endDt])
            ->selectRaw('COUNT(*) as total_trx, COALESCE(SUM(total),0) as pendapatan')
            ->first();

        $totalItems = DB::table('detail_transaksi as d')
            ->join('transaksi as t','t.idtransaksi','=','d.idtransaksi')
            ->whereBetween('t.tanggal', [$startDt, $endDt])
            ->sum('d.qty');

        // Omzet & jumlah trx per tanggal
        $rows = DB::table('transaksi')
            ->whereBetween('tanggal', [$startDt, $endDt])
            ->selectRaw('DATE(tanggal) as d, COALESCE(SUM(total),0) as omzet, COUNT(*) as trx')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        // Item terjual per tanggal
        $rowsItem = DB::table('detail_transaksi as d')
            ->join('transaksi as t','t.idtransaksi','=','d.idtransaksi')
            ->whereBetween('t.tanggal', [$startDt, $endDt])
            ->selectRaw('DATE(t.tanggal) as d, COALESCE(SUM(d.qty),0) as items')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $labels = [];
        $omzet  = [];
        $trx    = [];
        $items  = [];

        foreach (CarbonPeriod::create($startDt->copy()->startOfDay(), '1 day', $endDt->copy()->startOfDay()) as $day) {
            $key = $day->toDateString();
            $labels[] = $day->format('d M');
            $omzet[]  = (float) ($rows[$key]->omzet ?? 0);
            $trx[]    = (int)   ($rows[$key]->trx ?? 0);
            $items[]  = (int)   ($rowsItem[$key]->items ?? 0);
        }

        // Donut metode bayar
        $metode = DB::table('transaksi')
            ->whereBetween('tanggal', [$startDt, $endDt])
            ->selectRaw('metode_bayar, COALESCE(SUM(total),0) as total')
            ->groupBy('metode_bayar')
            ->pluck('total','metode_bayar')
            ->toArray();

        $donutLabels = ['tunai','qris'];
        $donutData   = [ (float)($metode['tunai'] ?? 0), (float)($metode['qris'] ?? 0) ];

        return view('laporan.index', [
            'title'         => 'Laporan',
            'start'         => Carbon::parse($start)->format('Y-m-d'),
            'end'           => Carbon::parse($end)->format('Y-m-d'),
            'tipe'          => $tipe,
            'totalTrx'      => (int) $ringkasan->total_trx,
            'pendapatan'    => (float) $ringkasan->pendapatan,
            'totalItems'    => (int) $totalItems,
            'labels'        => $labels,
            'seriesOmzet'   => $omzet,
            'seriesTrx'     => $trx,
            'seriesItems'   => $items,
            'donutLabels'   => $donutLabels,
            'donutData'     => $donutData,
        ]);
    }
}
