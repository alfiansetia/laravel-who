@extends('template', ['title' => 'Data Product'])
@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />
@endpush
@section('content')
    <div class="container-fluid">
        {{-- <h1>Data Product</h1> --}}

        <div class="responsive">
            <form id="selected">
                <table class="table table-sm table-hover" id="table" style="width: 100%;cursor: pointer;">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width: 30px;">#</th>
                            <th>KODE</th>
                            <th>NAME</th>
                            <th>AKL</th>
                            <th class="text-nowrap">AKL EXP</th>
                            <th>DESC</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    @include('product.modal')
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.products.index') }}";
        var id = 0
        $(document).ready(function() {
            lightbox.option({
                resizeDuration: 200,
                wrapAround: true
            });

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
                            // return `<input type="checkbox" name="id[]" value="${data}" class="new-control-input child-chk select-customers-info">`
                            return `
                            <div class="btn-group" role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-sm btn-info btn-info-product"><i class="fas fa-info-circle"></i></button>
                            <button type="button" class="btn btn-sm btn-primary btn-move"><i class="fas fa-arrows-alt-v"></i></button>
                            </div>
                            `
                        }
                    }, {
                        data: "code",
                        className: "text-left",
                    },
                    {
                        data: "name",
                        className: "text-left",
                    },
                    {
                        data: "akl",
                        className: "text-left",
                    },
                    {
                        data: "akl_exp",
                        className: "text-left",
                        render: function(data, type, row, meta) {
                            let text;
                            let now = moment(new Date()); //todays date
                            let end = moment(data); // another date
                            let duration = moment.duration(now.diff(end));
                            let days = duration.asDays();
                            if (days >= 0) {
                                text =
                                    `<span class="badge badge-danger">${data == null ? '' : data}</span>`;
                            } else {
                                text =
                                    `<span class="badge badge-success">${data == null ? '' : data}</span>`;
                            }
                            if (type == 'display') {
                                return text;
                            } else {
                                return data;
                            }
                        }
                    },
                    {
                        data: "desc",
                        visible: false
                    },
                ],
                buttons: [{
                        text: '<i class="fas fa-sync mr-1"></i>Sync from Odoo',
                        className: 'btn btn-sm btn-danger',
                        attr: {
                            'data-toggle': 'tooltip',
                            'title': 'Syncronize from Odoo'
                        },
                        action: function(e, dt, node, config) {
                            $.post("{{ route('api.products.sync') }}")
                                .done(function(res) {
                                    table.ajax.reload()
                                    show_message(res.message, 'success')
                                }).fail(function(xhr) {
                                    show_message(xhr.responseJSON.message || 'Error!')
                                });
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
                        text: '<i class="fas fa-download"></i> Export',
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
                    }
                ],
                // headerCallback: function(e, a, t, n, s) {
                //     e.getElementsByTagName("th")[0].innerHTML =
                //         '<input type="checkbox" class="new-control-input chk-parent select-customers-info" id="customer-all-info">'
                // },
            });


            var table_move = $('#table_move').DataTable({
                dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                oLanguage: {
                    "sSearchPlaceholder": "Search...",
                    "sLengthMenu": "Results :  _MENU_",
                },
                order: [
                    [2, "desc"]
                ],
                lengthMenu: [
                    [10, 50, 100, 500, 1000],
                    ['10 rows', '50 rows', '100 rows', '500 rows', '1000 rows']
                ],
                pageLength: 10,
                lengthChange: false,
                columns: [{
                        data: "reference",
                        className: "text-left",
                    }, {
                        data: "id",
                        className: 'text-left',
                        render: function(data, type, row, meta) {
                            if (row.location_dest_id != false) {
                                return row.location_dest_id[1]
                            } else {
                                return ''
                            }
                        }
                    }, {
                        data: "date",
                        className: "text-left",
                    }, {
                        data: "id",
                        className: 'text-left',
                        render: function(data, type, row, meta) {
                            if (row.lot_id != false) {
                                return row.lot_id[1]
                            } else {
                                return ''
                            }
                        }
                    },
                    {
                        data: "qty_done",
                        className: 'text-center',
                    },
                    {
                        data: "x_studio_no_so",
                        className: 'text-left',
                    }, {
                        data: "x_studio_customer",
                        className: 'text-left',
                    },

                ],
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
                    }
                ],
            });

            multiCheck(table);

            function deleteData() {

            }

            function selected() {
                let id = $('input[name="id[]"]:checked').length;
                if (id <= 0) {
                    show_message("No Selected Data!")
                    return false
                } else {
                    return true
                }
            }

            $('#table').on('click', '.btn-info-product', function() {
                let row = $(this).parents('tr')[0];
                data = table.row(row).data()
                id = data.id
                $.get(URL_INDEX_API + '/' + id).done(function(res) {
                    $('#table_pl tbody').empty();
                    $('#table_target tbody').empty();
                    $('#target_value').html('')
                    $('#table_pl_container').empty(); // clear container

                    res.data.packs.forEach((pack, packIndex) => {
                        let rows = '';

                        // Loop item utama dalam pack
                        pack.items.forEach((item, itemIndex) => {
                            // baris utama
                            rows += `
                                <tr>
                                    <td class="text-center">${itemIndex + 1}</td>
                                    <td>${item.item}</td>
                                    <td>${item.qty || ''}</td>
                                </tr>`;

                            // kalau ada sub-items
                            if (item.items && item.items.length > 0) {
                                item.items.forEach((sub, subIndex) => {
                                    rows += `
                                        <tr>
                                            <td class="text-center"></td>
                                            <td class="ps-4">↳ ${sub.item}</td>
                                            <td>${sub.qty || ''}</td>
                                        </tr>`;
                                });
                            }
                        });

                        // render tabel per pack
                        $('#table_pl_container').append(`
                            <div class="card mb-3 shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <strong>Pack ${packIndex + 1}: ${pack.name || '(Tanpa Nama)'}</strong>
                                    <button type="button" class="btn btn-xs btn-outline-info btn-print-pl" data-id="${pack.id}">
                                        <i class="fas fa-print"></i> Cetak PL
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="text-center" style="width: 40px;">No</th>
                                                <th>ITEM</th>
                                                <th>QTY</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${rows}
                                        </tbody>
                                    </table>
                                </div>
                            </div>`);
                    });

                    if (res.data.sop != null) {
                        $('#btn-print-sop').removeClass('d-none').data('id', res.data.sop.id);
                        $('#target_value').html(res.data.sop.target)
                        res.data.sop.items.forEach((item, index) => {
                            $('#table_target tbody').append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.item}</td>
                            </tr>
                        `);
                        });
                    } else {
                        $('#btn-print-sop').addClass('d-none');
                    }

                    let html = '';
                    if (res.data.images && res.data.images.length > 0) {
                        $('#btn-print-collage').removeClass('d-none').data('id', id);
                        res.data.images.forEach((img, i) => {
                            html += `
                                <a href="${img.url}" 
                                    data-lightbox="product-${id}" 
                                    data-title="Image [${res.data.code}] ${res.data.name} (${img.name})">
                                        <img src="${img.url}" 
                                            class="img-thumbnail"
                                            style="width:100px;height:100px;object-fit:cover;">
                                </a>
                            `;
                        });
                    } else {
                        $('#btn-print-collage').addClass('d-none');
                        html = '<p class="text-muted">Tidak ada gambar.</p>';
                    }
                    $('#detail_images').html(html);
                    $('#modal_pl').modal('show')

                }).fail(function(xhr) {
                    show_message('Data Tidak ada!')
                })

            });

            $('#btn-print-collage').on('click', function() {
                const productId = $(this).data('id');
                let url = "{{ route('product_images.collage', ':id') }}";
                url = url.replace(':id', productId);
                window.open(url, '_blank');
            });

            $('#table').on('click', '.btn-move', function() {
                let row = $(this).parents('tr')[0];
                data = table.row(row).data()
                id = data.id

                $.ajax({
                    url: `${URL_INDEX_API}/${id}/move`,
                    type: "GET",
                    success: function(res) {
                        table_move
                            .rows()
                            .remove()
                            .draw();
                        table_move
                            .rows
                            .add(res.data)
                            .draw()
                        $('#modal_move').modal('show')
                        // show_message(res.message, 'success')
                    },
                    error: function(xhr) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });



            });

            $('#table').on('click', '.btn-images', function() {
                let row = $(this).parents('tr')[0];
                data = table.row(row).data()
                id = data.id
                $.get(URL_INDEX_API + '/' + id).done(function(res) {
                    let html = '';
                    if (res.data.images && res.data.images.length > 0) {
                        res.data.images.forEach((img, i) => {
                            html += `
                                <a href="${img.url}" 
                                    data-lightbox="product-${id}" 
                                    data-title="Gambar ${i + 1}">
                                        <img src="${img.url}" 
                                            class="img-thumbnail"
                                            style="width:100px;height:100px;object-fit:cover;">
                                </a>
                            `;
                        });
                    } else {
                        html = '<p class="text-muted">Tidak ada gambar.</p>';
                    }
                    $('#detail_images').html(html);
                    if (typeof lightbox !== 'undefined') {
                        lightbox.option({
                            resizeDuration: 200,
                            wrapAround: true
                        });
                    }


                    $('#modal_images').modal('show')

                }).fail(function(xhr) {
                    show_message('Data Tidak ada!')
                })

            });

            $(document).on('click', '.btn-print-pl', function() {
                const id = $(this).data('id');
                window.open(`{{ url('packs') }}/${id}/print`, '_blank');
            });

            $('#btn-print-sop').on('click', function() {
                const id = $(this).data('id');
                window.open(`{{ url('sops') }}/${id}/print`, '_blank');
            });


        });
    </script>
@endpush
