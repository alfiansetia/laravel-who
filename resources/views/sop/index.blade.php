@extends('template', ['title' => 'SOP QC'])

@section('content')
    <div class="container-fluid">
        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">No</th>
                            <th>Kode Product</th>
                            <th>Name Product</th>
                            <th>Target</th>
                            <th>#</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
    @include('sop.modal_index')
@endsection

@push('js')
    <script>
        const URL_INDEX_API = "{{ route('api.sops.index') }}"
        const URL_INDEX = "{{ route('sops.index') }}"
        var id = 0;

        $(document).ready(function() {
            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: URL_INDEX_API,
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
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return `<input type="checkbox" name="id[]" value="${data}" class="new-control-input child-chk select-customers-info">`
                        }
                    }, {
                        data: "product.code",
                        className: 'text-left',
                    }, {
                        data: "product.name",
                        className: 'text-left',
                    },
                    {
                        data: "target",
                        className: 'text-left',
                    }, {
                        data: "id",
                        className: 'text-center',
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return `
                            <div class="btn-group" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-sm btn-info btn-download" title="Export Excel"><i class="fas fa-file-excel"></i></button>
                                <button type="button" class="btn btn-sm btn-secondary btn-print" title="Print HTML"><i class="fas fa-print"></i></button>
                            </div>
                            `
                        }
                    },
                ],
                buttons: [{
                        text: '<i class="fas fa-tasks mr-1"></i>Manage SOP QC',
                        className: 'btn btn-sm btn-info',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Manage SOP QC'
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
                        text: '<i class="fas fa-sync mr-1"></i>',
                        className: 'btn btn-sm btn-warning',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Refresh Data'
                        },
                        action: function(e, dt, node, config) {
                            table.ajax.reload()
                        }
                    },
                ],
                headerCallback: function(e, a, t, n, s) {
                    e.getElementsByTagName("th")[0].innerHTML =
                        '<input type="checkbox" class="new-control-input chk-parent select-customers-info" id="customer-all-info">'
                },
            });
            
            multiCheck(table);

            function selected() {
                let id = $('input[name="id[]"]:checked').length;
                if (id <= 0) {
                    show_message("No Selected Data!")
                    return false
                } else {
                    return true
                }
            }

            var id = 0;
            var currentSopData = {};

            window.addSopRow = function(content = '') {
                let rowCount = $('#table_sop_edit tbody tr').length + 1;
                $('#table_sop_edit tbody').append(`
                    <tr>
                        <td class="text-center align-middle font-weight-bold">${rowCount}</td>
                        <td>
                            <textarea class="form-control form-control-sm sop-item-text" rows="1" required placeholder="Instruksi pemeriksaan...">${content}</textarea>
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="$(this).closest('tr').remove()">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            }

            function fetchSopDetail(sopId) {
                $.get(URL_INDEX_API + '/' + sopId).done(function(res) {
                    let sop = res.data;
                    currentSopData = {
                        product_id: sop.product_id,
                        target: sop.target
                    };

                    // Header Info
                    $('#title_detail_product').html(`[${sop.product.code}] ${sop.product.name}`);
                    $('#target_value_display').html(sop.target || '-');
                    
                    // Input Target di tab edit
                    $('#input_target_sop').val(sop.target);

                    // Bersihkan tabel
                    $('#table_sop_view tbody').empty();
                    $('#table_sop_edit tbody').empty();

                    res.data.items.forEach((item, index) => {
                        // View Table
                        $('#table_sop_view tbody').append(`
                            <tr>
                                <td class="text-center text-muted">${index + 1}</td>
                                <td class="font-weight-bold">${item.item}</td>
                            </tr>
                        `);
                        // Edit Table
                        addSopRow(item.item);
                    });

                }).fail(function(xhr) {
                    show_message('Gagal mengambil data!', 'error')
                });
            }

            // Handle Tab Switching
            $(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
                let target = $(e.target).attr("href");
                if (id) fetchSopDetail(id);

                if (target === '#tab-edit') {
                    $('#btn_save_sop').removeClass('d-none');
                } else {
                    $('#btn_save_sop').addClass('d-none');
                }
            });

            // Handle Simpan SOP
            $('#btn_save_sop').click(function() {
                let items = [];
                let isValid = true;
                let target_val = $('#input_target_sop').val().trim();

                $('#table_sop_edit tbody tr').each(function() {
                    let text = $(this).find('.sop-item-text').val().trim();
                    if (!text) {
                        isValid = false;
                        $(this).find('.sop-item-text').addClass('is-invalid');
                    } else {
                        $(this).find('.sop-item-text').removeClass('is-invalid');
                        items.push({ item: text });
                    }
                });

                if (!isValid) {
                    show_message('Ada instruksi SOP yang kosong!', 'warning');
                    return;
                }

                $.ajax({
                    url: URL_INDEX_API,
                    type: "POST",
                    data: {
                        product_id: currentSopData.product_id,
                        target: target_val,
                        items: items
                    },
                    beforeSend: function() { bloc(); },
                    success: function(res) {
                        unbloc();
                        show_message(res.message, 'success');
                        $('#detail-tab').tab('show');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        unbloc();
                        show_message(xhr.responseJSON.message || 'Gagal menyimpan!');
                    }
                });
            });

            $('#table tbody').on('click', 'tr td:not(:last-child):not(:first-child)', function() {
                id = table.row(this).data().id
                
                // Reset State
                $('#detail-tab').tab('show');
                $('#btn_save_sop').addClass('d-none');
                $('#table_sop_view tbody').html('<tr><td colspan="2" class="text-center">Loading...</td></tr>');

                fetchSopDetail(id);
                $('#modal_pl').modal('show')

            });

            $('#table tbody').on('click', 'tr .btn-download', function() {
                row = $(this).parents('tr')[0];
                id = table.row(row).data().id
                window.open(`${URL_INDEX_API}/${id}/download`)
            });

            $('#table tbody').on('click', 'tr .btn-print', function() {
                row = $(this).parents('tr')[0];
                id = table.row(row).data().id
                window.open(`${URL_INDEX}/${id}/print`, '_blank')
            });

        });
    </script>
@endpush
