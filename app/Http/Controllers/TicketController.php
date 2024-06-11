<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ticket\TicketRequest;
use App\Models\JenisTicket;
use App\Models\Terusan;
use App\Models\Ticket;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ticket-access');
    }

    public function index()
    {
        $title = 'Data Ticket';
        $breadcrumbs = ['Master', 'Data Ticket'];

        return view('ticket.index', compact('title', 'breadcrumbs'));
    }

    public function get(Request $request)
    {
        if ($request->ajax()) {
            $data = Ticket::orderBy('tripod', 'asc')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('action', function ($row) {
                    $actionBtn = '<a href="' . route('tickets.edit', $row->id) . '" class="btn btn-sm btn-success btn-edit">Edit</a> ';
                    $actionBtn .= '<button type="button" data-route="' . route('tickets.destroy', $row->id) . '" class="delete btn btn-danger btn-delete btn-sm">Delete</button>';

                    return $actionBtn;
                })
                ->editColumn('harga', function ($row) {
                    return 'Rp. ' . number_format($row->harga, 0, ',', '.');
                })
                ->editColumn('jenis', function ($row) {
                    return $row->jenis->nama_jenis;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function create()
    {
        $title = 'Add Ticket';
        $breadcrumbs = ['Master', 'Add Ticket'];
        $action = route('tickets.store');
        $method = 'POST';
        $ticket = new Ticket();
        $jenis = JenisTicket::get();
        $terusan = Terusan::get();

        return view('ticket.form', compact('title', 'breadcrumbs', 'action', 'method', 'ticket', 'jenis', 'terusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'harga' => 'required|numeric',
            'jenis' => 'required|numeric',
            'tripod' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $ticket = Ticket::create([
                'name' => $request->name,
                'harga' => $request->harga,
                'jenis_ticket_id' => $request->jenis,
                'tripod' => $request->tripod,
            ]);

            $ticket->terusan()->sync($request->terusan);

            DB::commit();

            return redirect()->route('tickets.index')->with('success', "Ticket {$ticket->name} berhasil ditambahkan");
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function show(Ticket $ticket)
    {
        $title = 'Data Ticket';
        $breadcrumbs = ['Master', 'Data Ticket'];
        $action = 'asd';
        $method = 'post';

        return view('ticket.show', compact('ticket', 'title', 'breadcrumbs', 'action', 'method'));
    }

    public function edit(Ticket $ticket)
    {
        $title = 'Edit Ticket';
        $breadcrumbs = ['Master', 'Edit Ticket'];
        $action = route('tickets.update', $ticket->id);
        $method = 'PUT';
        $jenis = JenisTicket::get();
        $terusan = Terusan::get();

        return view('ticket.form', compact('title', 'breadcrumbs', 'action', 'method', 'ticket', 'jenis', 'terusan'));
    }

    public function update(TicketRequest $request, Ticket $ticket)
    {
        $request->validate([
            'name' => 'required|string',
            'harga' => 'required|numeric',
            'jenis' => 'required|numeric',
            'tripod' => 'required|numeric',
        ]);


        try {
            DB::beginTransaction();

            $ticket->update([
                'name' => $request->name,
                'harga' => $request->harga,
                'jenis_ticket_id' => $request->jenis,
                'tripod' => $request->tripod,
            ]);

            $ticket->terusan()->sync($request->terusan);

            DB::commit();

            return redirect()->route('tickets.index')->with('success', "Ticket {$ticket->name} berhasil diupdate");
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function destroy(Ticket $ticket)
    {
        try {
            DB::beginTransaction();

            $ticket->terusan()->detach();
            $ticket->delete();

            DB::commit();

            return redirect()->route('tickets.index')->with('success', "Ticket {$ticket->name} berhasil dihapus");
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->with('error', $th->getMessage());
        }
    }

    public function find(Ticket $ticket)
    {
        return response()->json([
            'status' => 'success',
            'ticket' => $ticket
        ], 200);
    }
}
