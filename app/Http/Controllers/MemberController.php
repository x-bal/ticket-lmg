<?php

namespace App\Http\Controllers;

use App\Http\Requests\Member\CreateMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Models\LimitMember;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:member-access')->except(['findOne']);
    }

    public function index()
    {
        $title = 'Data Member';
        $breadcrumbs = ['Master', 'Data Member'];
        $limit = LimitMember::first() ?? new LimitMember();

        return view('member.index', compact('title', 'breadcrumbs', 'limit'));
    }

    public function get(Request $request)
    {
        if ($request->ajax()) {
            $data = Member::orderBy('id', 'asc');

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = '<a href="#modal-dialog" id="' . $row->id . '" class="btn btn-sm btn-success btn-edit" data-route="' . route('members.update', $row->id) . '" data-bs-toggle="modal">Edit</a> <button type="button" data-route="' . route('members.destroy', $row->id) . '" class="delete btn btn-danger btn-delete btn-sm">Delete</button>';
                    return $actionBtn;
                })
                ->editColumn('harga', function ($row) {
                    return 'Rp. ' . number_format($row->harga, 0, ',', '.');
                })
                ->editColumn('expired', function ($row) {
                    if (Carbon::now('Asia/Jakarta')->format('Y-m-d') > $row->tgl_expired) {
                        return '<span class="badge bg-danger btn-expired fs-12px" data-route="' . route('members.expired', $row->id) . '">Expired</span>';
                    } else {
                        return '<span class="badge fs-12px bg-success">Active</span>';
                    }
                })
                ->editColumn('masa_berlaku', function ($row) {
                    return Carbon::parse($row->tgl_register)->format('d/m/Y') . ' - ' . Carbon::parse($row->tgl_expired)->format('d/m/Y');
                })
                ->editColumn('sisa_hari', function ($row) {
                    return Carbon::parse($row->tgl_expired)->diffInDays() . " Hari";
                })
                ->rawColumns(['action', 'expired'])
                ->make(true);
        }
    }

    public function store(CreateMemberRequest $request)
    {
        try {
            DB::beginTransaction();
            $attr = $request->except('tanggal_lahir');
            $attr['tgl_register'] = Carbon::now()->format('Y-m-d');
            $attr['tgl_expired'] = Carbon::now()->addMonth(1)->format('Y-m-d');
            $attr['tgl_lahir'] = request('tanggal_lahir');
            $attr['limit'] = request('limit');
            $attr['jenis_member'] = request('jenis_member');

            $member = Member::create($attr);

            DB::commit();

            return redirect()->route('members.index')->with('success', "Member {$member->nama} berhasil ditambahkan");
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function show(Member $member)
    {
        return response()->json([
            'status' => 'success',
            'member' => $member
        ], 200);
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        try {
            DB::beginTransaction();

            $member->update([
                'nama' => $request->nama,
                'no_ktp' => $request->no_ktp,
                'alamat' => $request->alamat,
                'tgl_lahir' => $request->tanggal_lahir,
                'limit' => $request->limit,
                'jenis_member' => $request->jenis_member,
            ]);

            DB::commit();

            return redirect()->route('members.index')->with('success', "Member {$member->nama} berhasil diupdate");
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function destroy(Member $member)
    {
        try {
            DB::beginTransaction();

            $member->delete();

            DB::commit();

            return redirect()->route('members.index')->with('success', "Member {$member->nama} berhasil dihapus");
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function findOne(Request $request)
    {
        $member = Member::where('rfid', $request->rfid)->first();

        if ($member) {
            return response()->json([
                'status' => 'success',
                'member' => $member
            ]);
        } else {
            return response()->json([
                'status' => 'error',
            ]);
        }
    }

    function expired(Member $member)
    {
        try {
            DB::beginTransaction();

            $member->update(['tgl_expired' => Carbon::now('Asia/Jakarta')->addMonth(6)->format('Y-m-d')]);

            DB::commit();
            return redirect()->route('members.index')->with('success', "Member {$member->nama} berhasil diperpanjang");
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    function update_setting(Request $request)
    {
        try {
            $limit = LimitMember::first();
            if ($limit) {
                $limit->update($request->all());
            } else {
                LimitMember::create($request->all());
            }

            DB::commit();
            return back()->with('success', "Setting member berhasil diupdate");
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }
}
