<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaction;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\ImagickEscposImage;

class DetailTransactionController extends Controller
{
    public function index(Request $request,  $id)
    {
        if ($request->ajax()) {
            $data = DetailTransaction::where('transaction_id', $id);

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->editColumn('action', function ($row) {
                    $actionBtn = '<button type="button" data-route="' . route('detail.destroy', $row->id) . '" class="delete btn btn-danger btn-delete btn-sm"><i class="fas fa-trash"></i></button>';
                    return $actionBtn;
                })
                ->editColumn('ticket', function ($row) {
                    return $row->ticket->name;
                })
                ->editColumn('qty', function ($row) {
                    return '<input type="number" name="qty" id="' . $row->id . '" class="form-control qty" value="' . $row->qty . '" autofocus>';
                })
                ->editColumn('harga', function ($row) {
                    return number_format($row->ticket->harga, 0, ',', '.');
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->ticket->harga * $row->qty, 0, ',', '.');
                })
                ->rawColumns(['action', 'qty'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $cek = DetailTransaction::where(['transaction_id' => $request->transaction, 'ticket_id' => $request->ticket])->first();

            // if ($cek) {
            //     $qty = $cek->qty;

            //     $cek->update([
            //         'qty' => $qty += 1
            //     ]);

            //     $cek->update([
            //         'total' => Ticket::find($request->ticket)->harga * $cek->qty
            //     ]);
            // } else {
            DetailTransaction::create([
                'transaction_id' => $request->transaction,
                'ticket_id' => $request->ticket,
                'ticket_code' => 'TKT' . date('YmdHis') . rand(100, 999),
                'qty' => 1,
                'total' => Ticket::find($request->ticket)->harga,
            ]);


            // }

            $amount = DetailTransaction::where(['transaction_id' => $request->transaction])->sum('qty');

            // if (!in_array($request->ticket, [11, 12])) {
            //     $asuransi = DetailTransaction::where(['transaction_id' => $request->transaction, 'ticket_id' => 13])->first();

            //     if ($asuransi) {
            //         $asuransi->update([
            //             'qty' => $amount - $asuransi->qty,
            //         ]);

            //         $asuransi->update([
            //             'total' => $asuransi->qty * Ticket::find($asuransi->ticket_id)->harga
            //         ]);
            //     }
            // }

            $detail = DetailTransaction::where('transaction_id', $request->transaction)->get();
            $totalPrice = DetailTransaction::where('transaction_id', $request->transaction)->sum('total');

            DB::commit();

            return response()->json([
                'status' => 'success',
                'detail' => $detail,
                'totalPrice' => number_format($totalPrice, 0, ',', '.'),
                'price' => $totalPrice
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ]);
        }
    }

    public function destroy(DetailTransaction $detailTransaction)
    {
        try {
            DB::beginTransaction();

            $asuransi = DetailTransaction::where(['transaction_id' => $detailTransaction->transaction_id, 'ticket_id' => 13])->first();

            $amount = DetailTransaction::where(['transaction_id' => $detailTransaction->transaction_id])->count('qty');

            if ($asuransi) {
                if ($amount == 1) {
                    $asuransi->update([
                        'qty' => ($asuransi->qty - $detailTransaction->qty) + 1,
                    ]);

                    $asuransi->update([
                        'total' => $asuransi->qty * Ticket::find($asuransi->ticket_id)->harga
                    ]);
                } else {
                    $asuransi->update([
                        'qty' => $asuransi->qty - $detailTransaction->qty
                    ]);

                    $asuransi->update([
                        'total' => $asuransi->qty * Ticket::find($asuransi->ticket_id)->harga
                    ]);
                }
            }

            $detailTransaction->delete();

            DB::commit();
            return back();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage()
            ]);
        }
    }

    public function save(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = Transaction::find($id);

            $now = Carbon::now()->format('Y-m-d');
            $lastTrx = Transaction::whereDate('created_at', $now)->orderBy('no_trx', 'DESC')->first()->no_trx ?? 0;
            $tickets = [];
            $idtrx = [];
            $print = $transaction->detail()->sum('qty') ?? 1;
            $tipe = 'individual';

            $totalHarga =  $transaction->detail()->sum('total');
            $firstTrx = $transaction->detail()->sum('qty');

            $discount = request('discount') ?? 0;
            $disc = request('discount') ? ($totalHarga * $discount) / 100 : 0;

            foreach ($transaction->detail as $detail) {
                for ($i = 1; $i <= $detail->qty; $i++) {
                    $tickets[] = [
                        "name" => $detail->ticket->name,
                        "harga" => number_format($detail->ticket->harga, 0, ',', '.'),
                        "ticket_code" => $detail->ticket_code
                    ];
                }
            }

            $transaction->update([
                'ticket_id' => 0,
                'amount' => $firstTrx,
                'is_active' => 1,
                'discount' => $discount,
                'disc' => $disc,
                'bayar' => str_replace('.', '', $request->bayar),
                'kembali' => str_replace('.', '', $request->kembali),
                'metode' => request('metode')
            ]);

            DB::commit();
            $setting = Setting::first();

            $logo = $setting ? asset('/storage/' . $setting->logo) : 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('/images/rio.png')));
            $ucapan = $setting->ucapan ?? 'Terima Kasih';
            $name = $setting->name ?? 'Ticketing';
            $deskripsi = $setting->deskripsi ?? 'qr code hanya berlaku satu kali';
            $use = $setting->use_logo ?? false;

            return view('transaction.print', compact('transaction', 'logo', 'ucapan', 'deskripsi', 'use', 'name', "tickets"));
            // $print = $this->print($transaction);
            // if ($print["status"] == "success") {
            //     return back()->with('success', "Transaction success");
            // } else {
            //     return back()->with('error', $print["message"]);
            // }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function remove(DetailTransaction $detailTransaction)
    {
        try {
            DB::beginTransaction();

            if (!in_array($detailTransaction->ticket_id, [11, 12])) {
                $qty = $detailTransaction->qty;

                $detailTransaction->update([
                    'qty' => $qty - 1
                ]);

                $detailTransaction->update([
                    'total' => $detailTransaction->qty * $detailTransaction->ticket->harga
                ]);

                $asuransi = DetailTransaction::where(['transaction_id' => $detailTransaction->transaction_id, 'ticket_id' => 13])->first();

                if ($asuransi) {
                    $asuransi->update([
                        'qty' => $asuransi->qty - 1
                    ]);

                    $asuransi->update([
                        'total' => $asuransi->qty * $asuransi->ticket->harga
                    ]);
                }
            }

            DB::commit();

            return back();
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function qty(Request $request)
    {
        try {
            DB::beginTransaction();

            $detail = DetailTransaction::find($request->id);
            $total = $request->qty * $detail->ticket->harga;
            $detail->update([
                'qty' => $request->qty,
                'total' => $total
            ]);

            $totalPrice = DetailTransaction::where('transaction_id', $detail->transaction_id)->sum('total');

            DB::commit();

            return response()->json([
                'status' => 'success',
                'totalPrice' => number_format($totalPrice, 0, ',', '.'),
                'price' => $totalPrice
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage()
            ]);
        }
    }

    function print($transaction)
    {
        // $transaction = Transaction::where(['is_active', 1, 'is_print' => 0, 'user_id' => auth()->user()->id])->first();

        $pathTransactions = [];
        $transactionFile = View::make('transaction.transaction')->with(['transaction' => $transaction])->render();
        $transactionPlain = strip_tags($transactionFile);

        $pathTransactions[] = [
            "invoice" => $transaction->ticket_code,
            "content" => $transactionPlain,
        ];

        foreach ($transaction->detail as $key => $detail) {
            $transactionDetailFile = View::make('transaction.detail')->with(['detail' => $detail])->render();
            $transactionDetailPlain = strip_tags($transactionDetailFile);

            $pathTransactions[] = [
                "invoice" => $detail->ticket_code,
                "content" => $transactionDetailPlain,
            ];
        }

        $print = $this->testPrint($pathTransactions);
        if ($print["status"] == "success") {
            return [
                "status" => "success"
            ];
        } else {
            return [
                "status" => "error",
                "message" => $print['message']
            ];
        }
    }

    function testPrint($pathTransactions)
    {
        try {
            $printerName = env('PRINTER');
            $connector = new WindowsPrintConnector($printerName);

            $printer = new Printer($connector);

            foreach ($pathTransactions as $path) {
                $printer->text($path["content"]);
                $printer->cut();
            }

            $printer->close();

            foreach ($pathTransactions as $path) {
                $invoiceCode = $path["invoice"];
                $transac = Transaction::where("ticket_code", $invoiceCode)->first();

                if ($transac) {
                    $transac->update(['is_print' => 1]);
                } else {
                    $detail = DetailTransaction::where('ticket_code', $invoiceCode)->first();
                    if ($detail) {
                        $detail->update(['is_print' => 1]);
                    }
                }
            }

            return [
                'status' => 'success'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
