@extends('template', ['title' => 'Data Product'])

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
                            <th>AKL EXP</th>
                            <th>DESC</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modal_pl" data-backdrop="static" data-keyboard="false" aria-labelledby="staticBackdropLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Product Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="accordion" id="accordionExample">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
                                        data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        SOP QC
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                data-parent="#accordionExample">
                                <div class="card-body">
                                    <h6 class="mb-2">Target : <span id="target_value"></span></h6>
                                    <table id="table_target" class="table table-sm table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="text-center" style="width: 30px;">No</th>
                                                <th>ITEM</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left collapsed" type="button"
                                        data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false"
                                        aria-controls="collapseTwo">
                                        Packing List
                                    </button>
                                </h2>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                data-parent="#accordionExample">
                                <div class="card-body" id="table_pl_container">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            const URL_INDEX_API = "{{ route('api.product.index') }}";
            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: "{{ route('api.product.index') }}",
                dom: "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
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
                // order: [
                //     [1, "asc"]
                // ],
                columns: [{
                        data: 'id',
                        className: "text-center",
                        searchable: false,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            // return `<input type="checkbox" name="id[]" value="${data}" class="new-control-input child-chk select-customers-info">`
                            return `<button type="button" class="btn btn-sm btn-info btn-info-product"><i class="fas fa-info-circle"></i></button>`
                        }
                    }, {
                        data: "code",
                    },
                    {
                        data: "name",
                    },
                    {
                        data: "akl",
                    },
                    {
                        data: "akl_exp",
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
                            $.post("{{ route('api.product.sync') }}")
                                .done(function(res) {
                                    table.ajax.reload()
                                    alert(res.message)
                                }).fail(function(xhr) {
                                    alert(xhr.responseJSON.message || 'Odoo Error!')
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

            var id;

            multiCheck(table);

            function deleteData() {

            }

            function selected() {
                let id = $('input[name="id[]"]:checked').length;
                if (id <= 0) {
                    alert("No Selected Data!")
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
                                <div class="card-header">
                                    <strong>Pack ${packIndex + 1}:</strong> ${pack.name || '(Tanpa Nama)'}
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
                        $('#target_value').html(res.data.sop.target)
                        res.data.sop.items.forEach((item, index) => {
                            $('#table_target tbody').append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.item}</td>
                            </tr>
                        `);
                        });
                    }
                    $('#modal_pl').modal('show')

                }).fail(function(xhr) {
                    alert('Data Tidak ada!')
                })

            });

        });
    </script>
@endpush
