<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Member;
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
                ->filterDaterange()
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
