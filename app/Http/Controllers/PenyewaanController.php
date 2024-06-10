<?php

namespace App\Http\Controllers;

use App\Models\HistoryPenyewaan;
use App\Models\Member;
use App\Models\Penyewaan;
use App\Models\Setting;
use App\Models\Sewa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PenyewaanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:penyewaan-access');
    }

    public function index()
    {
        $title = 'Data Penyewaan';
        $breadcrumbs = ['Master', 'Data Penyewaan'];
        $tickets = Sewa::get();

        return view('penyewaan.index', compact('title', 'breadcrumbs', 'tickets'));
    }

    public function get(Request $request)
    {
        if ($request->ajax()) {
            if ($request->tanggal) {
                $data = Penyewaan::whereDate('created_at', $request->tanggal)->orderBy('id', 'DESC');
            } else {
                $now = Carbon::now('Asia/Jakarta')->format('Y-m-d');
                $data = Penyewaan::whereDate('created_at', $now)->orderBy('id', 'DESC');
            }

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('penyewaan.print', $row->id) . '" class="btn btn-sm btn-primary">Print</a> ';

                    if (auth()->user()->can('penyewaan-delete')) {
                        $actionBtn .= '<button type="button" data-route="' . route('penyewaan.destroy', $row->id) . '" class="delete btn btn-danger btn-delete btn-sm">Delete</button>';
                    }
                    return $actionBtn;
                })
                ->editColumn('ticket', function ($row) {
                    return $row->sewa->name;
                })
                ->editColumn('harga', function ($row) {
                    return 'Rp. ' . number_format($row->sewa->harga, 0, ',', '.');
                })
                ->editColumn('jumlah', function ($row) {
                    return 'Rp. ' . number_format($row->jumlah, 0, ',', '.');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'ticket' => 'required|numeric',
                'qty' => 'required|numeric',
                'metode' => 'required|string',
                'jumlah' => 'required|string',
            ]);

            DB::beginTransaction();

            if ($request->metode == 'tap') {
                $member = Member::where('rfid', $request->name)->first();

                if ($member) {
                    if ($member->saldo > $request->jumlah) {
                        $penyewaan = Penyewaan::create([
                            'sewa_id' => $request->ticket,
                            'qty' => $request->qty,
                            'metode' => $request->metode,
                            'jumlah' => $request->jumlah
                        ]);

                        $member->update([
                            'saldo' => $member->saldo - $request->jumlah
                        ]);

                        HistoryPenyewaan::create([
                            'penyewaan_id' => $penyewaan->id,
                            'member_id' => $member->id,
                        ]);

                        DB::commit();

                        return back()->with('success', "Penyewaan berhasil");
                    } else {
                        return back()->with('error', "Saldo anda tidak mencukupi");
                    }
                } else {
                    return back()->with('error', "Member tidak ditemukan");
                }
            } else {
                $sewa = Penyewaan::create([
                    'sewa_id' => $request->ticket,
                    'qty' => $request->qty,
                    'metode' => $request->metode,
                    'jumlah' => str_replace('.', '', $request->jumlah),
                    'bayar' => str_replace('.', '', $request->bayar),
                    'kembali' => str_replace('.', '', $request->kembali),
                ]);

                DB::commit();

                return $this->print($sewa->id);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function print($id)
    {
        $penyewaan = Penyewaan::find($id);
        $setting = Setting::first();

        $logo = $setting ? asset('/storage/' . $setting->logo) : 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('/images/rio.png')));
        $name = $setting->name ?? 'Ticketing';
        $ucapan = $setting->ucapan ?? 'Terima Kasih';
        $deskripsi = $setting->deskripsi ?? 'qr code hanya berlaku satu kali';
        $use = $setting->use_logo ?? false;

        return view('penyewaan.print', compact('penyewaan', 'logo', 'name', 'use', 'ucapan', 'deskripsi'));
    }

    public function destroy(Penyewaan $penyewaan)
    {
        try {
            $history = HistoryPenyewaan::where('penyewaan_id', $penyewaan->id)->first();

            if ($history) {
                $member = Member::find($history->member_id);
                $member->update([
                    'saldo' => $member->saldo + $penyewaan->jumlah
                ]);

                $history->delete();
                $penyewaan->delete();

                DB::commit();

                return back()->with('success', "Penyewaan berhasil dihapus");
            } else {
                $penyewaan->delete();

                DB::commit();
                return back()->with('success', "Penyewaan berhasil dihapus");
            }
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }
}
