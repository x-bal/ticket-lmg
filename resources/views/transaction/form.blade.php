@extends('layouts.master', ['title' => $title, 'breadcrumbs' => $breadcrumbs])

@push('style')
<link href="{{ asset('/') }}plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="{{ asset('/') }}plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title">{{ $title }}</h4>
        <div class="panel-heading-btn">
            <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-warning" data-toggle="panel-collapse"><i class="fa fa-minus"></i></a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-danger" data-toggle="panel-remove"><i class="fa fa-times"></i></a>
        </div>
    </div>

    <div class="panel-body">
        <form action="{{ route('detail.save', $transaction->id) }}" method="post">
            @csrf
            <div class="row">
                <h2 class="mb-3">Total Price : <span id="price">Rp. {{ number_format($transaction->detail()->sum('total'), 0, ',','.') }}</span></h2>
                <div class="col-md-6">
                    <table class="table table-bordered" id="datatable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>QTY</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>

                    <div class="form-group mb-3">
                        <label for="discount">Discount</label><br>
                        <button type="button" class="btn btn-sm btn-success btn-discount" id="10">10%</button>
                        <button type="button" class="btn btn-sm btn-success btn-discount" id="20">20%</button>
                        <button type="button" class="btn btn-sm btn-success btn-discount" id="30">30%</button>
                        <button type="button" class="btn btn-sm btn-success btn-discount" id="50">50%</button>

                        <input type="number" name="discount" id="discount" class="form-control mt-3" value="0" readonly>
                        <input type="hidden" name="disc" id="disc" value="0">
                    </div>

                    <div class="form-group mb-3">
                        <label for="metode">Metode Pembayaran</label>
                        <select name="metode" id="metode" class="form-control">
                            <option value="cash">Cash</option>
                            <option value="debit">Debit</option>
                            <option value="kredit">Kredit</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="bayar">Bayar</label>
                        <input type="text" name="bayar" id="bayar" class="form-control" value="{{ $transaction->detail()->sum('total') ?? 0 }}" autofocus>
                    </div>

                    <div class="form-group mb-3">
                        <label for="kembali">Kembali</label>
                        <input type="text" name="kembali" id="kembali" class="form-control" value="0" readonly>
                    </div>

                    <input type="hidden" name="totalPrice" value="{{ $transaction->detail()->sum('total') }}" id="totalPrice">

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-save">Submit</button>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="content-ticket row">
                        @foreach($tickets as $ticket)
                        <div class="col-md-4 mb-2">
                            <button type="button" id="{{ $ticket->id }}" class="btn btn-primary text-center w-100 h-100 btn-ticket">
                                <span>
                                    <span class="d-block" style="text-transform: uppercase;"><b>{{ $ticket->name }}</b></span>
                                    <span class="d-block fs-12px opacity-7">{{ number_format($ticket->harga,0, ',', '.') }}</span>
                                </span>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<form action="" class="d-none" id="form-delete" method="post">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('script')
<script src="{{ asset('/') }}plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('/') }}plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('/') }}plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="{{ asset('/') }}plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script src="{{ asset('/') }}plugins/sweetalert/dist/sweetalert.min.js"></script>

<script>
    $(document).ready(function() {

        localStorage.setItem("total", "{{ $total }}")

        function getData() {
            $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('detail.list', $transaction->id) }}",
                deferRender: true,
                pagination: true,
                bDestroy: true,
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        sortable: false,
                        searchable: false
                    },
                    {
                        data: 'ticket',
                        name: 'ticket',
                    },
                    {
                        data: 'qty',
                        name: 'qty',
                    },
                    {
                        data: 'harga',
                        name: 'harga',
                    },
                    {
                        data: 'action',
                        name: 'action',
                    },
                ]
            });
        }

        getData()

        $(".btn-ticket").on('click', function() {
            let id = $(this).attr('id');
            let trx = "{{ $transaction->id }}";

            $.ajax({
                url: '/transaction/create',
                type: "GET",
                method: "GET",
                data: {
                    ticket: id,
                    transaction: trx
                },
                success: function(response) {
                    price = response.totalPrice;
                    $("#price").empty().append('Rp. ' + price)
                    $("#totalPrice").empty().val(response.price)
                    $("#bayar").empty().val(price)
                    localStorage.clear("total")
                    localStorage.setItem("total", response.price)
                    getData()
                }
            })
        })

        $("#datatable").on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let route = $(this).attr('data-route')
            $("#form-delete").attr('action', route)

            swal({
                title: 'Hapus data transaction?',
                text: 'Menghapus transaction bersifat permanen.',
                icon: 'error',
                buttons: {
                    cancel: {
                        text: 'Cancel',
                        value: null,
                        visible: true,
                        className: 'btn btn-default',
                        closeModal: true,
                    },
                    confirm: {
                        text: 'Yes',
                        value: true,
                        visible: true,
                        className: 'btn btn-danger',
                        closeModal: true
                    }
                }
            }).then((result) => {
                if (result) {
                    $("#form-delete").submit()
                } else {
                    $("#form-delete").attr('action', '')
                }
            });
        })

        $("#bayar").on('input', function() {
            let bayar = $(this).val().replace('.', '');
            let price = $("#totalPrice").val()
            let kembali = parseInt(bayar - price)
            $("#kembali").val((kembali / 1000).toFixed(3))
        })

        var rupiah = document.getElementById('bayar');
        rupiah.addEventListener('keyup', function(e) {
            // tambahkan 'Rp.' pada saat form di ketik
            // gunakan fungsi formatRupiah() untuk mengubah angka yang di ketik menjadi format angka
            rupiah.value = formatRupiah(this.value);
        });

        /* Fungsi formatRupiah */
        function formatRupiah(angka) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            // tambahkan titik jika yang di input sudah menjadi angka ribuan
            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return undefined ? rupiah : (rupiah ? rupiah : '');
        }

        $("#datatable").on('change', '.qty', function() {
            let qty = $(this).val();
            let id = $(this).attr('id');

            $.ajax({
                url: "{{ route('detail.qty') }}",
                method: "GET",
                type: "GET",
                data: {
                    id: id,
                    qty: qty,
                },
                success: function(response) {
                    $("#price").empty().append('Rp. ' + response.totalPrice)
                    $("#totalPrice").val(response.price)
                    localStorage.clear("total")
                    localStorage.setItem("total", response.price)
                    getData()
                }
            })
        })

        $(".btn-discount").on('click', function() {
            let disc = $(this).attr('id');
            $("#discount").val(disc)
            let total = localStorage.getItem("total")
            // $(".btn-save").removeAttr("href")
            // $(".btn-save").attr("href", "{{ route('detail.save', $transaction->id) }}?discount=" + disc)

            let diskon = (disc * total / 100)
            hasil = total - diskon;
            $("#disc").val(diskon)
            $("#totalPrice").val(hasil)
            $("#price").empty().append('Rp. ' + (hasil / 1000).toFixed(3))
        })
    })
</script>

<script>

</script>
@endpush