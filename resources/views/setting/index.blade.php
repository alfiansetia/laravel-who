@extends('template')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-8">
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
                                    <textarea class="form-control" id="odoo_env" placeholder="ODOO SESSION" rows="4" required></textarea>
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
            </div>
            <div class="col-md-4">
                <div class="card card-primary mt-3">
                    <div class="card-header mb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-server mr-1"></i>Resource</h4>
                        <button type="button" id="resource_refresh" class="btn btn-sm btn-warning">
                            <i class="fas fa-sync mr-1"></i>
                        </button>
                    </div>
                    <div class="card-body" id="resource-body">
                        <p class="text-muted mb-0">Loading data...</p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-primary mt-3">
                    <div class="card-header mb-0">
                        <h4 class="mb-0"><i class="fas fa-laptop mr-1"></i>List Device</h4>
                    </div>
                    <div class="card-body">
                        <div class="responsive">
                            <form id="selected">
                                <table class="table table-sm table-hover" id="table"
                                    style="width: 100%;cursor: pointer;">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-center" style="width: 30px;">No</th>
                                            <th>Platform</th>
                                            <th>User Agent</th>
                                            <th>IP</th>
                                            <th>Token</th>
                                            <th>Last Status</th>
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
            <div class="col-md-4">
                <div class="card card-primary mt-3">
                    <div class="card-header mb-0 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-history mr-1"></i>Logs</h4>
                        <button type="button" id="btn_clear_log" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash mr-1"></i>
                        </button>
                    </div>

                    <div class="card-body">
                        <textarea name="" id="log" class="form-control" rows="6"></textarea>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="modal_detail" data-backdrop="static" tabindex="-1" aria-labelledby="modal_detailLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_detailLabel"><i class="fas fa-info mr-1"></i>Detail Token</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row">
                    <div class="form-group col-6">
                        <label for="platform">Platform</label>
                        <input name="platform" id="platform" class="form-control">
                    </div>
                    <div class="form-group col-6">
                        <label for="ip">IP Address</label>
                        <input name="ip" id="ip" class="form-control">
                    </div>
                    <div class="form-group col-12">
                        <label for="user_agent">User Agent</label>
                        <div class="input-group">
                            <textarea name="user_agent" id="user_agent" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="form-group col-12">
                        <label for="token">
                            Token <span id="token_status" class="badge badge-success" style="display: none">Device
                                Ini</span>
                        </label>
                        <div class="input-group">
                            <textarea name="token" id="token" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="form-group col-12">
                        <label for="last_status">Last Status</label>
                        <div class="input-group">
                            <input name="last_status" id="last_status" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-12">
                        <label for="last_status_at">Last Status At</label>
                        <div class="input-group">
                            <input name="last_status_at" id="last_status_at" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
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
            getData();
            getResource();

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

            $('#resource_refresh').click(function() {
                getResource()
            })

            function getResource() {
                $.ajax({
                    url: "{{ route('api.resources.index') }}",
                    type: 'GET',
                    beforeSend: function() {
                        $('#resource-body').html('<p class="text-muted mb-0">Loading data...</p>');
                    },
                    success: function(res) {
                        const data = res.data;
                        let html = `
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="p-3 rounded-lg border shadow-sm d-flex justify-content-between align-items-center" style="background: #e8f1ff;">
                                        <div>
                                            <h6 class="mb-1 text-primary font-weight-bold">Products</h6>
                                            <small class="text-muted">${data.products.value.toLocaleString()} bytes</small>
                                        </div>
                                        <span class="badge badge-primary px-3 py-2">${data.products.parse}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-3 rounded-lg border shadow-sm d-flex justify-content-between align-items-center" style="background: #f5f6f7;">
                                        <div>
                                            <h6 class="mb-1 text-secondary font-weight-bold">Logs</h6>
                                            <small class="text-muted">${data.logs.value.toLocaleString()} bytes</small>
                                        </div>
                                        <span class="badge badge-secondary px-3 py-2">${data.logs.parse}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#log').val(res.data.logs.content);
                        $('#resource-body').html(html);
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Gagal mengambil data resource';
                        $('#resource-body').html(`<p class="text-danger mb-0">${message}</p>`);
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
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (!data) return '';
                            let text = data.length > 20 ? data.substr(0, 20) + '...' : data;
                            return text
                        }
                        return data;
                    }
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
                    data: "last_status",
                    className: 'text-left',
                    render: function(data, type, row, meta) {
                        if (type === 'display') {
                            return `<span title="${row.last_status_at}">${data}</span>`
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
                                <button type="button" class="btn btn-sm btn-primary btn-info"><i class="fas fa-info-circle"></i></button>
                            </div>
                            `
                    }
                }, ],
                buttons: [{
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

            $('#table tbody').on('click', 'tr td:not(:last-child):not(:first-child)', function() {
                data = table.row(this).data()
                $('#platform').val(data.platform)
                $('#ip').val(data.ip)
                $('#user_agent').val(data.user_agent)
                $('#token').val(data.token)
                $('#last_status').val(data.last_status)
                $('#last_status_at').val(data.last_status_at)
                if (currentToken == data.token) {
                    $('#token_status').show()
                } else {
                    $('#token_status').hide()
                }
                $('#modal_detail').modal('show')
            });



            $('#btn_clear_log').click(function() {
                confirmation('Clear Log?', function(confirm) {
                    if (confirm) {
                        $.ajax({
                            url: "{{ route('api.resources.destroy_log') }}",
                            type: "DELETE",
                            success: function(res) {
                                getResource();
                                show_message(res.message, 'success')
                            },
                            error: function(xhr) {
                                show_message(xhr.responseJSON.message || 'Error!')
                            }
                        });
                    }
                })
            })




        });
    </script>
@endpush
