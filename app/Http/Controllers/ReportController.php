<?php

namespace App\Http\Controllers;

use App\Exports\PenyewaanExport;
use App\Exports\TransactionExport;
use App\Models\Penyewaan;
use App\Models\Sewa;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function transaction(Request $request)
    {
        $date = $request->from ? Carbon::parse($request->from)->format('d/m/Y') . ' s.d ' . Carbon::parse($request->to)->format('d/m/Y') : Carbon::now()->format('d/m/Y');

        $title = 'Report Transaction ' . $date;
        $breadcrumbs = ['Master', 'Report Transaction'];
        $users = User::get();

        return view('report.transaction', compact('title', 'breadcrumbs', 'users'));
    }

    public function transactionList(Request $request)
    {
        if ($request->ajax()) {
            $now = Carbon::now()->format('Y-m-d');

            if ($request->from && $request->to && $request->kasir == 'all') {
                $to = Carbon::parse($request->to)->addDay(1)->format('Y-m-d');

                $data = Transaction::where('is_active', 1)->whereBetween('created_at', [$request->from, $to]);
            } elseif ($request->from && $request->to && $request->kasir != 'all') {
                $to = Carbon::parse($request->to)->addDay(1)->format('Y-m-d');
                $data = Transaction::where(['is_active' => 1, 'user_id' => $request->kasir])->whereBetween('created_at', [$request->from, $to]);
            } else {
                $data = Transaction::where('is_active', 1)->whereDate('created_at', $now);
            }

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('tanggal', function ($row) {
                    return Carbon::parse($row->created_at)->format('d/m/Y H:i:s');
                })
                ->editColumn('ticket', function ($row) {
                    return $row->ticket->name ?? '-';
                })
                ->editColumn('harga', function ($row) {
                    return 'Rp. ' . number_format($row->ticket->harga ?? 0, 0, ',', '.');
                })
                ->editColumn('jumlah', function ($row) {
                    return 'Rp. ' . number_format($row->detail()->sum('total'), 0, ',', '.') ?? 0;
                })
                ->editColumn('discount', function ($row) {
                    return 'Rp. ' . number_format($row->detail()->sum('total') * $row->discount / 100, 0, ',', '.') ?? 0;
                })
                ->editColumn('harga_ticket', function ($row) {
                    $disc = $row->detail()->sum('total') * $row->discount / 100;
                    return 'Rp. ' . number_format($row->detail()->sum('total') - $disc, 0, ',', '.') ?? 0;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function penyewaan(Request $request)
    {
        $date = $request->from ? Carbon::parse($request->from)->format('d/m/Y') . ' s.d ' . Carbon::parse($request->to)->format('d/m/Y') : Carbon::now()->format('d/m/Y');

        $title = 'Report Penyewaan ' . $date;
        $breadcrumbs = ['Master', 'Report Penyewaan'];
        $users = User::get();

        return view('report.penyewaan', compact('title', 'breadcrumbs', 'users'));
    }

    public function penyewaanList(Request $request)
    {
        if ($request->ajax()) {
            $now = Carbon::now()->format('Y-m-d');

            if ($request->from && $request->to && $request->kasir == 'all') {
                $to = Carbon::parse($request->to)->addDay(1)->format('Y-m-d');

                $data = Penyewaan::whereBetween('created_at', [$request->from, $to]);
            } elseif ($request->from && $request->to && $request->kasir != 'all') {
                $to = Carbon::parse($request->to)->addDay(1)->format('Y-m-d');

                $data = Penyewaan::where('user_id', $request->kasir)->whereBetween('created_at', [$request->from, $to]);
            } else {
                $data = Penyewaan::whereDate('created_at', $now);
            }

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('tanggal', function ($row) {
                    return Carbon::parse($row->created_at)->format('d/m/Y H:i:s');
                })
                ->editColumn('sewa', function ($row) {
                    return $row->sewa->name;
                })
                ->editColumn('harga', function ($row) {
                    return 'Rp. ' . number_format($row->sewa->harga, 0, ',', '.');
                })
                ->editColumn('total', function ($row) {
                    return 'Rp. ' . number_format($row->jumlah, 0, ',', '.');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function rekapTransaction(Request $request)
    {
        $date = $request->from ? Carbon::parse($request->from)->format('d/m/Y') . ' s.d ' . Carbon::parse($request->to)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $from = $request->from ? Carbon::parse($request->from)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $to = $request->to ? Carbon::parse($request->to)->addDay(1)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        $title = 'Rekap Transaction ' . $date;
        $breadcrumbs = ['Master', 'Rekap Transaction'];
        $tickets = Ticket::get();
        $users = User::get();

        return view('report.rekap-transaction', compact('title', 'breadcrumbs', 'from', 'to', 'tickets', 'users'));
    }

    public function exportTransaction(Request $request)
    {
        $from = Carbon::parse($request->from)->format('Y-m-d');
        $to = Carbon::parse($request->to)->addDay(1)->format('Y-m-d');

        return Excel::download(new TransactionExport($from, $to, $request->kasir), 'Rekap Transaction.xlsx');
    }

    function printTransaction(Request $request)
    {
        $from = Carbon::parse($request->from)->format('Y-m-d');
        $to = Carbon::parse($request->to)->addDay(1)->format('Y-m-d');
        $tickets = Ticket::whereNotIn('id', [14, 15, 16])->get();
        $kasir = $request->kasir;

        return view('report.download-transaction', compact('tickets', 'from', 'to', 'kasir'));
    }

    public function rekapPenyewaan(Request $request)
    {
        $date = $request->from ? Carbon::parse($request->from)->format('d/m/Y') . ' s.d ' . Carbon::parse($request->to)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $from = $request->from ? Carbon::parse($request->from)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $to = $request->to ? Carbon::parse($request->to)->addDay(1)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        $title = 'Rekap Penyewaan ' . $date;
        $breadcrumbs = ['Master', 'Rekap Penyewaan'];
        $sewa = Sewa::get();
        $users = User::get();

        return view('report.rekap-penyewaan', compact('title', 'breadcrumbs', 'from', 'to', 'sewa', 'users'));
    }

    public function exportPenyewaan(Request $request)
    {
        $from = Carbon::parse(request('from'))->format('Y-m-d');
        $to = Carbon::parse(request('to'))->addDay(1)->format('Y-m-d');

        return Excel::download(new PenyewaanExport($from, $to, $request->kasir), 'Rekap Penyewaan.xlsx');
    }

    public function printPenyewaan(Request $request)
    {
        $from = Carbon::parse(request('from'))->format('Y-m-d');
        $to = Carbon::parse(request('to'))->addDay(1)->format('Y-m-d');
        $kasir = $request->kasir;
        $sewa = Sewa::get();

        return view('report.download-penyewaan', compact('from', 'to', 'kasir', 'sewa'));
    }
}
