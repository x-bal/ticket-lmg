<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class HistoryController extends Controller
{
    function index()
    {
        $title = 'History Member';
        $breadcrumbs = ['Report', 'History Member'];
        $members = Member::get();

        $start = request('daterange') ? Carbon::createFromFormat('m/d/Y', explode(' - ', request('daterange'))[0])->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = request('daterange') ? Carbon::createFromFormat('m/d/Y', explode(' - ', request('daterange'))[1])->format('Y-m-d') : Carbon::now()->endOfMonth()->format('Y-m-d');

        return view('history.index', compact('title', 'breadcrumbs', 'members', 'start', 'end'));
    }

    function list(Request $request)
    {
        if ($request->ajax()) {
            $data = History::with('member')
                ->where('member_id', '!=', 0)
                ->filterDaterange()
                ->filterKaryawan()
                ->orderBy('waktu', 'DESC');

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('waktu', function ($row) {
                    return Carbon::parse($row->waktu)->format('d/m/Y H:i:s');
                })
                // ->rawColumns([''])
                ->make(true);
        }
    }

    function karyawan()
    {
        $title = 'History Karyawan';
        $breadcrumbs = ['Report', 'History Karyawan'];
        $users = User::get();

        $start = request('daterange') ? Carbon::createFromFormat('m/d/Y', explode(' - ', request('daterange'))[0])->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = request('daterange') ? Carbon::createFromFormat('m/d/Y', explode(' - ', request('daterange'))[1])->format('Y-m-d') : Carbon::now()->endOfMonth()->format('Y-m-d');

        return view('history.karyawan', compact('title', 'breadcrumbs', 'users', 'start', 'end'));
    }

    function list_karyawan(Request $request)
    {
        if ($request->ajax()) {
            $data = History::with('user')
                ->where('user_id', '!=', 0)
                ->filterDaterange()
                ->filterKaryawan()
                ->orderBy('waktu', 'DESC');

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('waktu', function ($row) {
                    return Carbon::parse($row->waktu)->format('d/m/Y H:i:s');
                })
                // ->rawColumns([''])
                ->make(true);
        }
    }
}
