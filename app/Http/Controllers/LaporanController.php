<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LaporanController extends Controller
{
    public function index(Request $r)
    {
        // 1) Tipe periode valid sesuai enum
        $allow = ['Harian','Mingguan','Bulanan','Tahunan'];
        $tipe  = $r->input('tipe_periode', 'Harian');
        if (!in_array($tipe, $allow, true)) $tipe = 'Harian';

        // 2) Tanggal awal/akhir (default awal bulan ini s/d hari ini)
        $fromIn = trim((string)$r->input('start', ''));
        $toIn   = trim((string)$r->input('end',   ''));
        $from   = $fromIn !== '' ? Carbon::parse($fromIn) : Carbon::now()->startOfMonth();
        $to     = $toIn   !== '' ? Carbon::parse($toIn)   : Carbon::now();

        // pastikan from <= to
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $fromDate = $from->toDateString();
        $toDate   = $to->toDateString();

        // 3) Helper grouping + daftar bucket lengkap agar chart tidak kosong
        [$selectGrp, $groupExpr, $phpKeyFn, $phpLabelFn, $bucketKeys] = $this->groupingHelpers($tipe, $from, $to);

        // 4) Query agregat per bucket
        $rows = DB::table('detail_transaksi as d')
            ->join('transaksi as t', 't.idtransaksi', '=', 'd.idtransaksi')
            ->whereBetween(DB::raw('DATE(t.tanggal)'), [$fromDate, $toDate])
            ->select([
                $selectGrp, // AS grp
                DB::raw('SUM(d.subtotal) AS omzet'),
                DB::raw('SUM(d.qty)      AS item_terjual'),
                DB::raw('COUNT(DISTINCT t.idtransaksi) AS trx_count'),
            ])
            ->groupBy($groupExpr)
            ->orderBy($groupExpr)
            ->get();

        // 5) Map hasil -> isi semua bucket (yang tidak ada diisi 0)
        $mapOmzet = []; $mapItems = []; $mapTrx = [];
        foreach ($rows as $row) {
            $k = (string)$row->grp;
            $mapOmzet[$k] = (float)$row->omzet;
            $mapItems[$k] = (float)$row->item_terjual;
            $mapTrx[$k]   = (int)$row->trx_count;
        }

        $labels = []; $seriesOmzet = []; $seriesTrx = []; $seriesItems = [];
        foreach ($bucketKeys as $bk) {
            $labels[]      = $phpLabelFn($bk);
            $seriesOmzet[] = $mapOmzet[$bk] ?? 0.0;
            $seriesTrx[]   = $mapTrx[$bk]   ?? 0;
            $seriesItems[] = $mapItems[$bk] ?? 0.0;
        }

        // 6) KPI cards
        $totalTrx   = array_sum($seriesTrx);
        $pendapatan = array_sum($seriesOmzet);
        $totalItems = array_sum($seriesItems);

        // 7) Donut metode bayar
        $pay = DB::table('transaksi')
            ->whereBetween(DB::raw('DATE(tanggal)'), [$fromDate, $toDate])
            ->selectRaw('LOWER(metode_bayar) as m, SUM(total) as total')
            ->groupBy('m')
            ->pluck('total', 'm');

        $donutLabels = ['Tunai','QRIS'];
        $donutData   = [
            (float)($pay['tunai'] ?? 0),
            (float)($pay['qris']  ?? 0),
        ];

        return view('laporan.index', [
            'tipe_periode' => $tipe,
            'start'        => $fromDate,
            'end'          => $toDate,

            'labels'       => $labels,
            'seriesOmzet'  => $seriesOmzet,
            'seriesTrx'    => $seriesTrx,
            'seriesItems'  => $seriesItems,

            'totalTrx'     => $totalTrx,
            'pendapatan'   => $pendapatan,
            'totalItems'   => $totalItems,

            'donutLabels'  => $donutLabels,
            'donutData'    => $donutData,
        ]);
    }

    /**
     * Menghasilkan:
     * - $selectGrp (DB::raw ... AS grp)
     * - $groupExpr (GROUP BY)
     * - $phpKeyFn(Carbon) -> key string
     * - $phpLabelFn(string key) -> label
     * - $bucketKeys array key lengkap untuk range
     */
    private function groupingHelpers(string $tipe, Carbon $from, Carbon $to): array
    {
        switch ($tipe) {
            case 'Mingguan':
                // SQL pakai ISO week: YEARWEEK(..., 3)
                $selectGrp = DB::raw('YEARWEEK(t.tanggal, 3) AS grp');
                $groupExpr = DB::raw('YEARWEEK(t.tanggal, 3)');

                $phpKeyFn = function (Carbon $d): string {
                    return (string) ($d->isoWeekYear * 100 + $d->isoWeek); // YYYYWW
                };
                $phpLabelFn = function (string $k): string {
                    $v = (int)$k; $y = (int)floor($v/100); $w = $v % 100;
                    return "Minggu $w $y";
                };

                $cursor = $from->copy()->startOfWeek(Carbon::MONDAY);
                $end    = $to->copy()->endOfWeek(Carbon::SUNDAY);
                $bucket = [];
                while ($cursor->lte($end)) {
                    $bucket[] = $phpKeyFn($cursor);
                    $cursor->addWeek();
                }
                return [$selectGrp, $groupExpr, $phpKeyFn, $phpLabelFn, array_values(array_unique($bucket))];

            case 'Bulanan':
                $selectGrp = DB::raw("DATE_FORMAT(t.tanggal, '%Y-%m') AS grp");
                $groupExpr = DB::raw("DATE_FORMAT(t.tanggal, '%Y-%m')");

                $phpKeyFn   = fn(Carbon $d) => $d->format('Y-m');
                $phpLabelFn = fn(string $k) => date('M Y', strtotime($k.'-01'));

                $cursor = $from->copy()->startOfMonth();
                $end    = $to->copy()->endOfMonth();
                $bucket = [];
                while ($cursor->lte($end)) {
                    $bucket[] = $phpKeyFn($cursor);
                    $cursor->addMonth();
                }
                return [$selectGrp, $groupExpr, $phpKeyFn, $phpLabelFn, $bucket];

            case 'Tahunan':
                $selectGrp = DB::raw('YEAR(t.tanggal) AS grp');
                $groupExpr = DB::raw('YEAR(t.tanggal)');

                $phpKeyFn   = fn(Carbon $d) => $d->format('Y');
                $phpLabelFn = fn(string $k) => $k;

                $cursor = $from->copy()->startOfYear();
                $end    = $to->copy()->endOfYear();
                $bucket = [];
                while ($cursor->lte($end)) {
                    $bucket[] = $phpKeyFn($cursor);
                    $cursor->addYear();
                }
                return [$selectGrp, $groupExpr, $phpKeyFn, $phpLabelFn, $bucket];

            case 'Harian':
            default:
                $selectGrp = DB::raw('DATE(t.tanggal) AS grp');
                $groupExpr = DB::raw('DATE(t.tanggal)');

                $phpKeyFn   = fn(Carbon $d) => $d->format('Y-m-d');
                $phpLabelFn = fn(string $k) => date('d M Y', strtotime($k));

                $period = CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay());
                $bucket = [];
                foreach ($period as $d) $bucket[] = $phpKeyFn($d);
                return [$selectGrp, $groupExpr, $phpKeyFn, $phpLabelFn, $bucket];
        }
    }
}
