@extends('template')

@section('content')
    <div class="container-fluid">
        <form method="POST" action="" id="form">
            @csrf
            <div class="card card-primary mt-3">
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <input type="text" disabled class="form-control" id="odoo_session_name"
                                placeholder="ODOO SESSION USER">
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" disabled class="form-control" id="odoo_session_username"
                                placeholder="ODOO SESSION USERNAME">
                        </div>
                        <div class="form-group col-md-6">
                            <textarea class="form-control" id="odoo_env" placeholder="ODOO SESSION" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                    <button type="button" id="btn_refresh" class="btn btn-warning">
                        <i class="fas fa-sync mr-1"></i>Refresh
                    </button>
                    <button type="button" id="btn_fix" class="btn btn-danger">
                        <i class="fas fa-hammer mr-1"></i>Fix Session
                    </button>
                    <button type="button" id="btn_notif" class="btn btn-info">
                        <i class="fas fa-bell mr-1"></i>Tes Notif
                    </button>
                    <button type="submit" id="btn_simpan" class="btn btn-primary">
                        <i class="fab fa-telegram-plane mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </form>

        <div class="card card-primary mt-3">
            <div class="card-header mb-0">
                <h3>List Device</h3>
            </div>
            <div class="card-body">
                <div class="responsive">
                    <form id="selected">
                        <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center" style="width: 30px;">No</th>
                                    <th>Platform</th>
                                    <th>User Agent</th>
                                    <th>IP</th>
                                    <th>Token</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const URL_INDEX_API = "{{ route('api.settings.index') }}"
        const currentToken = localStorage.getItem('fcm_token');
        $(document).ready(function() {
            getData()

            function getData() {
                $.ajax({
                    url: URL_INDEX_API,
                    type: 'GET',
                    beforeSend: function() {},
                    success: function(res) {
                        $('#odoo_env').val(res.data.session_id)
                        $('#odoo_session_username').val(res.data.username)
                        $('#odoo_session_name').val(`${res.data.name} (${res.data.uid})`)
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            }

            $('#btn_refresh').click(function() {
                getData()
            })

            $('#form').submit(function(e) {
                e.preventDefault()
                let odoo_env = $('#odoo_env').val();
                $.ajax({
                    url: URL_INDEX_API,
                    type: 'POST',
                    data: {
                        env_value: odoo_env,
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })

            $('#btn_fix').click(function() {
                $.ajax({
                    url: URL_INDEX_API,
                    type: 'PUT',
                    beforeSend: function() {},
                    success: function(res) {
                        getData();
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })

            $('#btn_notif').click(function() {
                $.ajax({
                    url: URL_INDEX_API,
                    type: 'DELETE',
                    beforeSend: function() {},
                    success: function(res) {
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })

            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: "{{ route('api.tokens.index') }}",
                dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                oLanguage: {
                    "sSearchPlaceholder": "Search...",
                    "sLengthMenu": "Results :  _MENU_",
                },
                lengthMenu: [
                    [10, 50, 100, 500, 1000],
                    ['10 rows', '50 rows', '100 rows', '500 rows', '1000 rows']
                ],
                pageLength: 10,
                lengthChange: false,
                order: [
                    [1, "asc"]
                ],
                columns: [{
                    data: 'id',
                    className: "text-center",
                    width: '30px',
                    searchable: false,
                    sortable: false,
                    render: function(data, type, row, meta) {
                        return `<input type="checkbox" name="id[]" value="${data}" class="new-control-input child-chk select-customers-info">`
                    }
                }, {
                    data: "platform",
                    className: 'text-center',
                }, {
                    data: "user_agent",
                    className: 'text-left',
                }, {
                    data: "ip",
                    className: 'text-left',
                }, {
                    data: "token",
                    className: 'text-left',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) return '';
                            let shortToken = data.length > 20 ? data.substr(0, 20) + '...' :
                                data;
                            if (currentToken && data.trim() === currentToken.trim()) {
                                return `<span class="badge bg-success mr-1">Device ini</span>${shortToken}`;
                            }
                            return shortToken;
                        }
                        return data;
                    }
                }, {
                    data: "id",
                    className: 'text-center',
                    searchable: false,
                    sortable: false,
                    render: function(data, type, row, meta) {
                        return `
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-sm btn-primary btn-edit"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete"><i class="fas fa-trash"></i></button>
                            </div>
                            `
                    }
                }, ],
                buttons: [{
                        text: '<i class="fas fa-plus mr-1"></i>Add PL',
                        className: 'btn btn-sm btn-info',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Add PL'
                        },
                        action: function(e, dt, node, config) {
                            window.location.href = URL_INDEX + '/create'
                        }
                    }, {
                        extend: "colvis",
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Column Visible'
                        },
                        className: 'btn btn-sm btn-primary'
                    },
                    {
                        extend: "pageLength",
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Page Length'
                        },
                        className: 'btn btn-sm btn-info'
                    },
                    {
                        extend: "collection",
                        text: '<i class="fas fa-download mr-1"></i>Export',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Export Data'
                        },
                        className: 'btn btn-sm btn-primary',
                        buttons: [{
                            extend: 'copy',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'csv',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'pdf',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'excel',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'print',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }],
                    }, {
                        text: '<i class="fa fa-tools"></i> Action',
                        className: 'btn btn-sm btn-warning bs-tooltip',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Action'
                        },
                        extend: 'collection',
                        autoClose: true,
                        buttons: [{
                            text: '<i class="fas fa-sync mr-1"></i>Refresh Data',
                            className: 'btn btn-sm btn-warning',
                            attr: {
                                'data-toggle': 'tooltip',
                                'title': 'Refresh Data'
                            },
                            action: function(e, dt, node, config) {
                                table.ajax.reload()
                            }
                        }, {
                            text: '<i class="fas fa-trash mr-1"></i>Delete Selected Data',
                            className: 'btn btn-sm btn-danger',
                            attr: {
                                'data-toggle': 'tooltip',
                                'title': 'Delete Selected Data'
                            },
                            action: function(e, dt, node, config) {
                                // change_data()
                            }
                        }]
                    },
                ],
                headerCallback: function(e, a, t, n, s) {
                    e.getElementsByTagName("th")[0].innerHTML =
                        '<input type="checkbox" class="new-control-input chk-parent select-customers-info" id="customer-all-info">'
                },
            });

            multiCheck(table);

        });
    </script>
@endpush
