@php
date_default_timezone_set('Asia/Jakarta')
@endphp
<!DOCTYPE html>
<html>

<head>
    <title>Print QR</title>
    <style>
        @media print {
            .row {
                page-break-after: always;
            }

            .row .qr-code {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <div class="row" style="max-height:150mm !important;">
        <div style="max-width:72mm !important;  margin: 0 auto 0 auto; vertical-align: top; border-style: solid;border-width: 1px;">
            <div style="widht:72mm; margin-top:10px;margin-bottom:0px">
                <div style="float: left;">
                    <!-- <img src="{{ asset('/images/rio.png') }}" width="50" alt="The Logo" class="brand-image" style="opacity: .8; margin-top: -15px !important;"> -->
                    @if($use == 1)
                    <img src="{{ $logo }}" width="55" alt="The Logo" class="brand-image" style="opacity: .8; margin-top: -15px !important;">
                    @else
                    <img src="" alt="">
                    @endif
                </div>
                <div style="float: right; margin-right:5px">
                    <p style="font-size: 8.5pt;text-transform: uppercase; margin: 1px 1px 1px 1px;">{{ $name }}</p>
                    <p style="font-size: 8.5pt; margin-top:1px;margin-bottom: 5px;">{{ date('d/m/Y H:i:s', strtotime($penyewaan->created_at)) }} </p>
                    <p style="font-size: 8.5pt;margin: 1px 1px 5px 1px;"> </p>
                </div>
            </div>

            <div style="widht:72mm; font-size:8pt; padding:2mm 0 2mm 0; margin-top: 40px; margin-bottom: 0px; ">
                <hr style="border-style: dashed;">
                <p style="text-align:center;font-size:12pt;font-weight:bold;text-transform: uppercase;margin-bottom:0px">{{ $penyewaan->sewa->name }}
                </p>
                <br><br>

                <p style="font-size:10pt;margin-left:10px;margin-top:5px;margin-bottom:0px">Jumlah Sewa <span style="float: right; margin-right: 20px;">{{ $penyewaan->qty . ' X ' . number_format($penyewaan->sewa->harga, 0, ',', '.') }}</span></p>


                <p style="font-size:10pt;margin-left:10px;margin-top:5px;margin-bottom:0px">Jumlah <span style="float: right; margin-right: 20px;"> {{ number_format($penyewaan->jumlah, 0 , ',', '.') }}</span></p>

                <p style="font-size:10pt;margin-left:10px;margin-top:5px;margin-bottom:0px">Total Bayar <span style="float: right; margin-right: 20px; font-weight: bold;">Rp. {{ number_format($penyewaan->jumlah, 0 , ',', '.') }}</span></p>

                <p style="font-size:10pt;margin-left:10px;margin-top:5px;margin-bottom:0px"></p>
                <br>
                <p style="font-size:10pt;text-align: center;margin-top:5px">*** {{ $ucapan }} ***</p>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            window.print()
        })
    </script>
</body>

</html>