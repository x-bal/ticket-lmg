<!DOCTYPE html>
<html>

<head>
    <title>Print QR</title>
    <style>
        @media print {
            .ticket-row {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <div class="ticket-row" style="margin-top: 10px;">
        <div class="qr-code" style="max-width:80mm !important;  margin: 0 auto 0 auto; vertical-align: top; border-style: solid;border-width: 1px;">
            <div class="detail" style="font-size: 10pt; line-height: 18px; margin-top: 10px; margin-bottom: 10px;">
                <span style="display: flex; text-align: center; font-weight: 900; font-size: 10pt; margin-bottom: 10px; justify-content: space-between; margin-left: 10px; margin-right: 10px;">
                    @if($use == 1)
                    <img src="{{ $logo }}" width="55" alt="The Logo" class="brand-image" style="opacity: .8; margin-top: -15px !important;">
                    @else
                    <img src="" alt="">
                    @endif

                    <span>
                        <span style="display: block;">{{ $transaction->ticket_code }}</span>
                        <span>{{ $transaction->created_at->format('d/m/Y H:i:s') }}</span>
                    </span>
                </span>
                <div style="display: flex;font-weight: 900; justify-content: space-between; margin-left: 10px; margin-right: 10px;">
                    <span>Jumlah Ticket : </span>
                    <span>{{ $transaction->detail()->count() }}</span>
                </div>
                <div style="display: flex;font-weight: 900; justify-content: space-between; margin-left: 10px; margin-right: 10px;">
                    <span>Total Harga : </span>
                    <span>Rp. {{ number_format($transaction->detail()->sum('total'), 0, ',', '.') }}</span>
                </div>
                <div style="display: flex;font-weight: 900; justify-content: space-between; margin-left: 10px; margin-right: 10px;">
                    <span>Discount : </span>
                    @php
                    $discount = $transaction->discount * $transaction->detail()->sum('total') / 100;
                    @endphp
                    <span>Rp. {{ number_format($discount, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex;font-weight: 900; justify-content: space-between; margin-left: 10px; margin-right: 10px;">
                    <span>Bayar : </span>
                    <span>Rp. {{ number_format($transaction->bayar, 0, ',', '.') }}</span>
                </div>
                <div style="display: flex;font-weight: 900; justify-content: space-between; margin-left: 10px; margin-right: 10px;">
                    <span>Kembali : </span>
                    <span>Rp. {{ number_format($transaction->kembali, 0, ',', '.') }}</span>
                </div>
                <hr style="border-style: dashed;">
                <p style="font-size:9pt;text-align: center;margin-bottom:10px; text-transform: uppercase;">{{ $ucapan }}</p>
            </div>
        </div>
    </div>

    @foreach($tickets as $detail)
    <div class="ticket-row" style="margin-top: 10px;">
        <div class="qr-code" style="max-width:80mm !important;  margin: 0 auto 0 auto; vertical-align: top; border-style: solid;border-width: 1px;">
            <div class="detail" style="font-size: 10pt; line-height: 18px;">
                <span style="display: block; text-align: center; font-weight: 900;">{{ $name }}</span>
                <span style="display: block; text-align: center; font-weight: 900;">{{ $detail["name"] }}</span>
                <span style="display: block; text-align: center;">Rp. {{ $detail["harga"] }}</span>
                <span style="display: block; text-align: center;"></span>
            </div>
            <hr style="border-style: dashed;">
            <p style="text-align: center; margin-top: 15px; margin-bottom: 15px">
                {!! QrCode::size(100)->generate($detail["ticket_code"]) !!}
                <br>
                <span>{{ $detail["ticket_code"] }}</span>
            </p>

            <hr style="border-style: dashed;">
            <p style="font-size:9pt;text-align: center;margin-bottom:10px; text-transform: uppercase;">{{ $deskripsi }}</p>
        </div>
    </div>
    @endforeach

    <script src="{{ asset('/js/jquery.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            window.print();
            setTimeout(function() {
                document.location.href = "{{ route('transactions.create') }}";
            }, 3000)
        })
    </script>
</body>

</html>