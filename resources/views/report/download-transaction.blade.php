<style>
    .table {
        max-width: 80mm !important;
        margin: 0 auto 0 auto;
        vertical-align: top;
        border-style: solid;
        border-width: 1px;
    }

    @media print {
        .table {
            max-width: 72mm !important;
            margin-left: -10px !important;
        }
    }
</style>

<table class="table" border="1" cellspacing="0" cellpadding="12">
    <thead>
        <tr>
            <th colspan="5">Report Transaction <br> Tanggal {{ Carbon\Carbon::parse($from)->format('d/m/Y') }} - {{ request('to') ? Carbon\Carbon::parse($to)->subDay(1)->format('d/m/Y') : Carbon\Carbon::parse($to)->format('d/m/Y') }}</th>
        </tr>
        <tr>
            <th>Jenis Ticket</th>
            <th class="text-center">Jumlah</th>
            <th class="text-center">Harga Ticket</th>
            <th class="text-end">Total Harga Ticket</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tickets as $ticket)
        <tr>
            <td>{{ $ticket->name }}</td>

            @if($kasir == 'all')
            @php
            $idtrx = App\Models\Transaction::where(['is_active' => 1])->whereBetween('created_at', [$from, $to])->pluck('id');
            @endphp
            @elseif($kasir != 'all')
            @php
            $idtrx = App\Models\Transaction::where(['is_active' => 1, 'user_id' => $kasir])->whereBetween('created_at', [$from, $to])->pluck('id');
            @endphp
            @else
            @php
            $idtrx = App\Models\Transaction::where(['is_active' => 1])->whereBetween('created_at', [$from, $to])->pluck('id');
            @endphp
            @endif

            <td class="text-center">{{ App\Models\DetailTransaction::whereIn('transaction_id', $idtrx)->where('ticket_id', $ticket->id)->sum('qty') }}</td>
            <td class="text-center">{{ number_format($ticket->harga, 0, ',', '.') }}</td>
            <td class="text-end">
                {{ number_format(App\Models\DetailTransaction::whereIn('transaction_id', $idtrx)->where('ticket_id', $ticket->id)->sum('total'), 0, ',', '.') ?? 0 }}
            </td>
        </tr>
        @endforeach

        @if(request('from') && request('to') && request('kasir') == 'all')
        @php
        $idtrxx = App\Models\Transaction::where(['is_active' => 1])->whereBetween('created_at', [$from, $to])->pluck('id');
        $cashid = App\Models\Transaction::where(['is_active' => 1, 'metode' => 'cash'])->whereBetween('created_at', [$from, $to])->pluck('id');
        $debitid = App\Models\Transaction::where(['is_active' => 1, 'metode' => 'debit'])->whereBetween('created_at', [$from, $to])->pluck('id');
        $kreditid = App\Models\Transaction::where(['is_active' => 1, 'metode' => 'kredit'])->whereBetween('created_at', [$from, $to])->pluck('id');
        $qrisid = App\Models\Transaction::where(['is_active' => 1, 'metode' => 'qris'])->whereBetween('created_at', [$from, $to])->pluck('id');
        @endphp
        @elseif(request('from') && request('to') && request('kasir') != 'all')
        @php
        $idtrxx = App\Models\Transaction::where(['is_active' => 1, 'user_id' => request('kasir')])->whereBetween('created_at', [$from, $to])->pluck('id');
        $cashid = App\Models\Transaction::where(['is_active' => 1, 'user_id' => request('kasir'), 'metode' => 'cash'])->whereBetween('created_at', [$from, $to])->pluck('id');
        $debitid = App\Models\Transaction::where(['is_active' => 1, 'user_id' => request('kasir'), 'metode' => 'debit'])->whereBetween('created_at', [$from, $to])->pluck('id');
        $kreditid = App\Models\Transaction::where(['is_active' => 1, 'user_id' => request('kasir'), 'metode' => 'kredit'])->whereBetween('created_at', [$from, $to])->pluck('id');
        $qrisid = App\Models\Transaction::where(['is_active' => 1, 'user_id' => request('kasir'), 'metode' => 'qris'])->whereBetween('created_at', [$from, $to])->pluck('id');
        @endphp
        @else
        @php
        $idtrxx = App\Models\Transaction::where(['is_active' => 1])->whereBetween('created_at', [$from, $to])->pluck('id');
        $cashid = App\Models\Transaction::where(['is_active' => 1, 'metode' => 'cash'])->whereBetween('created_at', [$from, $to])->pluck('id');
        $debitid = App\Models\Transaction::where(['is_active' => 1, 'metode' => 'debit'])->whereBetween('created_at', [$from, $to])->pluck('id');
        $kreditid = App\Models\Transaction::where(['is_active' => 1, 'metode' => 'kredit'])->whereBetween('created_at', [$from, $to])->pluck('id');
        $qrisid = App\Models\Transaction::where(['is_active' => 1, 'metode' => 'qris'])->whereBetween('created_at', [$from, $to])->pluck('id');
        @endphp
        @endif

        <tr>
            <th>Total Penjualan :</th>
            <th class="text-center">
                <b>{{ App\Models\DetailTransaction::whereIn('transaction_id', $idtrxx)->sum('qty') }}</b>
            </th>
            <th></th>
            <th class="text-end">
                <b>{{ number_format(App\Models\DetailTransaction::whereIn('transaction_id', $idtrxx)->sum('total'), 0, ',', '.') }}</b>
            </th>
        </tr>

        <tr>
            <th colspan="3">Total Discount :</th>
            <th class="text-end">
                <b>{{ number_format(App\Models\Transaction::whereIn('id', $idtrxx)->sum('disc'), 0, ',', '.') }}</b>
            </th>
        </tr>

        <tr>
            <th>Metode Pembayaran :</th>
            <th class="text-center">Cash</th>
            <th colspan="2" class="text-end">
                {{ number_format(App\Models\DetailTransaction::whereIn('transaction_id', $cashid)->sum('total'), 0, ',', '.') }}
            </th>
        </tr>

        <tr>
            <th rowspan="3"></th>
            <th class="text-center">Debit</th>
            <th colspan="2" class="text-end">
                {{ number_format(App\Models\DetailTransaction::whereIn('transaction_id', $debitid)->sum('total'), 0, ',', '.') }}
            </th>
        </tr>

        <tr>
            <th class="text-center">Kredit</th>
            <th colspan="2" class="text-end">
                {{ number_format(App\Models\DetailTransaction::whereIn('transaction_id', $kreditid)->sum('total'), 0, ',', '.') }}
            </th>
        </tr>

        <tr>
            <th class="text-center">QRIS</th>
            <th colspan="2" class="text-end">
                {{ number_format(App\Models\DetailTransaction::whereIn('transaction_id', $qrisid)->sum('total'), 0, ',', '.') }}
            </th>
        </tr>

        <tr>
            <th colspan="3">Total Amount :</th>
            <th class="text-end">
                <b>{{ number_format(App\Models\DetailTransaction::whereIn('transaction_id', $idtrxx)->sum('total') - App\Models\Transaction::whereIn('id', $idtrxx)->sum('disc'), 0, ',', '.') }}</b>
            </th>
        </tr>
    </tbody>
</table>

<script src="{{ asset('/js/jquery.min.js') }}"></script>

<script>
    $(document).ready(function() {
        window.print();
    })
</script>