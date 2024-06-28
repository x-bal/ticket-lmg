@extends('layouts.master', ['title' => $title, 'breadcrumbs' => $breadcrumbs])

@push('style')
<link href="{{ asset('/') }}plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" />
<link href="{{ asset('/') }}plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" />

<style>
    .btn-expired {
        cursor: pointer;
    }
</style>
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
        <a href="#modal-dialog" id="btn-add" class="btn btn-primary mb-3" data-route="{{ route('members.store') }}" data-bs-toggle="modal"><i class="ion-ios-add"></i> Add Member</a>

        <table id="datatable" class="table table-striped table-bordered align-middle">
            <thead>
                <tr>
                    <th class="text-nowrap">No</th>
                    <th class="text-nowrap">Rfid</th>
                    <th class="text-nowrap">Name</th>
                    <th class="text-nowrap">No Ktp</th>
                    <th class="text-nowrap">No Hp</th>
                    <th class="text-nowrap">Jenis Member</th>
                    <th class="text-nowrap">Masa Berlaku</th>
                    <th class="text-nowrap">Sisa Hari</th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="modal fade" id="modal-dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Form Member</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <form action="" method="post" id="form-member">
                    @csrf

                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="rfid">Rfid</label>
                            <input type="text" name="rfid" id="rfid" class="form-control" value="">

                            @error('rfid')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="nama">Nama</label>
                            <input type="text" name="nama" id="nama" class="form-control" value="">

                            @error('nama')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="no_ktp">No Ktp</label>
                            <input type="number" name="no_ktp" id="no_ktp" class="form-control" value="">

                            @error('no_ktp')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="no_hp">No Hp</label>
                            <input type="number" name="no_hp" id="no_hp" class="form-control" value="">

                            @error('no_hp')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="alamat">Alamat</label>
                            <textarea name="alamat" id="alamat" cols="30" rows="3" class="form-control"></textarea>

                            @error('alamat')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" value="">

                            @error('tanggal_lahir')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="jenis_member">Jenis Member</label>
                            <select name="jenis_member" id="jenis_member" class="form-control">
                                <option value="member">Member Bulanan</option>
                                <option value="guru">Guru Renang</option>
                            </select>

                            @error('jenis_member')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="limit">Limit Harian Member</label>
                            <input type="number" name="limit" id="limit" class="form-control" value="">

                            @error('limit')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="tgl_expired">Tanggal Expired</label>
                            <input type="date" name="tgl_expired" id="tgl_expired" class="form-control" value="">

                            @error('tgl_expired')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:;" id="btn-close" class="btn btn-white" data-bs-dismiss="modal">Close</a>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<form action="" class="d-none" id="form-delete" method="post">
    @csrf
    @method('DELETE')
</form>
<form action="" class="d-none" id="form-expired" method="post">
    @csrf
</form>
@endsection

@push('script')
<script src="{{ asset('/') }}plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('/') }}plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('/') }}plugins/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="{{ asset('/') }}plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<script src="{{ asset('/') }}plugins/sweetalert/dist/sweetalert.min.js"></script>

<script>
    var table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('members.list') }}",
        deferRender: true,
        pagination: true,
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                sortable: false,
                searchable: false
            },
            {
                data: 'rfid',
                name: 'rfid'
            },
            {
                data: 'nama',
                name: 'nama'
            },
            {
                data: 'no_ktp',
                name: 'no_ktp'
            },
            {
                data: 'no_hp',
                name: 'no_hp'
            },
            {
                data: 'jenis_member',
                name: 'jenis_member'
            },
            {
                data: 'masa_berlaku',
                name: 'masa_berlaku'
            },
            {
                data: 'sisa_hari',
                name: 'sisa_hari'
            },
            {
                data: 'expired',
                name: 'expired',
                sortable: false,
                searchable: false
            },
            {
                data: 'action',
                name: 'action',
                sortable: false,
                searchable: false
            },
        ]
    });

    $("#btn-add").on('click', function() {
        $("#rfid").removeAttr('disabled');
        let route = $(this).attr('data-route')
        $("#form-member").attr('action', route)
    })

    $("#btn-close").on('click', function() {
        $("#form-member").removeAttr('action')
    })

    $("#datatable").on('click', '.btn-edit', function() {
        $("#rfid").attr('disabled', 'disabled');
        let route = $(this).attr('data-route')
        let id = $(this).attr('id')

        $("#form-member").attr('action', route)
        $("#form-member").append(`<input type="hidden" name="_method" value="PUT">`);

        $.ajax({
            url: "/members/" + id,
            type: 'GET',
            method: 'GET',
            success: function(response) {
                console.log(response)
                let member = response.member;

                $("#rfid").val(member.rfid)
                $("#nama").val(member.nama)
                $("#no_ktp").val(member.no_ktp)
                $("#no_hp").val(member.no_hp)
                $("#alamat").val(member.alamat)
                $("#tanggal_lahir").val(member.tgl_lahir)
                $("#limit").val(parseInt(member.limit))
                $("#jenis_member").val(member.jenis_member)
                $("#tgl_expired").val(member.tgl_expired)
            }
        })
    })

    $("#datatable").on('click', '.btn-delete', function(e) {
        e.preventDefault();
        let route = $(this).attr('data-route')
        $("#form-delete").attr('action', route)

        swal({
            title: 'Hapus data member?',
            text: 'Menghapus member bersifat permanen.',
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

    $("#datatable").on('click', '.btn-expired', function(e) {
        e.preventDefault();
        let route = $(this).attr('data-route')
        $("#form-expired").attr('action', route)

        swal({
            title: 'Perpanjang data member?',
            icon: 'warning',
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
                    className: 'btn btn-primary',
                    closeModal: true
                }
            }
        }).then((result) => {
            if (result) {
                $("#form-expired").submit()
            } else {
                $("#form-expired").attr('action', '')
            }
        });
    })

    // $('#form-member').on('keyup keypress', function(e) {
    //     var keyCode = e.keyCode || e.which;
    //     if (keyCode === 13) {
    //         e.preventDefault();
    //         return false;
    //     }
    // });

    $("#rfid").on('keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    })
</script>
@endpush