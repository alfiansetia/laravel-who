@extends('template', ['title' => 'Manage SOP QC'])
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        .input-group>.select2-container--bootstrap {
            width: auto;
            flex: 1 1 auto;
        }

        .input-group>.select2-container--bootstrap .select2-selection--single {
            height: 100%;
            line-height: inherit;
            padding: 0.5rem 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <form method="POST" action="{{ route('api.sops.store') }}" id="form">
            @csrf
            <div class="card card-primary mt-3">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-6">
                            <label for="product_id">Product</label>
                            <div class="input-group">
                                <select name="product_id" id="product_id" class="custom-select select2" style="width: 100%"
                                    required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $item)
                                        <option data-id="{{ $item->id }}" data-code="{{ $item->code }}"
                                            data-name="{{ $item->name }}" value="{{ $item->id }}">
                                            {{ $item->code }} {{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-6">
                            <div class="input-group">
                                <label for="target">TARGET</label>
                                <div class="input-group">
                                    <input name="target" id="target" class="form-control" required>
                                    <div class="input-group-append">
                                        <button type="button" id="btn_target_clear" class="input-group-text">CLEAR</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-lg-6">
                            <label for="import">IMPORT FROM TEXT</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="button" id="btn_import_clear" class="input-group-text">CLEAR</button>
                                </div>
                                <textarea name="import" id="import" class="form-control"></textarea>
                                <div class="input-group-append">
                                    <button type="button" id="btn_import" class="input-group-text">IMPORT</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('sops.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                    <a href="{{ route('sops.create') }}" class="btn btn-warning">
                        <i class="fas fa-sync mr-1"></i>Refresh
                    </a>
                    <button type="submit" id="btn_simpan" class="btn btn-primary">
                        <i class="fab fa-telegram-plane mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </form>

        <div class="card card-primary mt-3">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table" class="table table-sm table-hover" style="width: 100%;cursor: pointer;">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 30px">#</th>
                                <th>ITEM</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('sop.modal_create')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#product_id').select2({
                theme: 'bootstrap4',
            }).on('change', function() {
                get_data()
            });

            var table = $('#table').DataTable({
                pageLength: false,
                lengthChange: false,
                ordering: false,
                paging: false,
                searching: false,
                info: false,
                dom: "<'dt--top-section'<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                columns: [{
                    data: "item",
                    width: '30px',
                    searchable: false,
                    sortable: false,
                    render: function() {
                        return '<span class="text-danger del-item"><i class="fas fa-trash"></i></span>'
                    }
                }, {
                    data: "item",
                    className: "text-left",
                }, ],
                buttons: [{
                    text: '<i class="fas fa-plus mr-1"></i>Add ITEM',
                    className: 'btn btn-sm btn-info',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Add ITEM'
                    },
                    action: function(e, dt, node, config) {
                        $('#modal_item').modal('show')
                    }
                }, {
                    text: '<i class="fas fa-trash mr-1"></i>Empty ITEM',
                    className: 'btn btn-sm btn-danger',
                    attr: {
                        'data-toggle': 'tooltip',
                        'title': 'Empty ITEM'
                    },
                    action: function(e, dt, node, config) {
                        table
                            .rows()
                            .remove()
                            .draw();
                    }
                }],
            });

            $('#modal_item').on('shown.bs.modal', function() {
                $('#item').focus()
            });

            $('#form_item').submit(function(e) {
                e.preventDefault()
                let item = $('#item').val()
                if (item == '' || item == null) {
                    show_message('Item empty!')
                    $('#item').focus()
                    return
                }
                console.log(item);
                table.row.add({
                    item: item,
                }).draw()
                $('#item').val('')
                $('#modal_item').modal('hide')
            })

            $('#table').on('click', '.del-item', function() {
                let row = $(this).parents('tr')[0];
                table
                    .row($(this).parents('tr'))
                    .remove()
                    .draw();

            });

            $('#form').submit(function(e) {
                e.preventDefault()
                let product = $('#product_id').val()
                let target = $('#target').val()
                let desc = $('#desc').val()
                let data = table.rows().data().toArray();
                if (product == '' || product == null) {
                    show_message('select Product!')
                    return
                }

                if (target == '' || target == null) {
                    show_message('Target Empty!')
                    return
                }
                if (data.length < 1) {
                    show_message('Item Empty!')
                    return
                }
                $.ajax({
                    type: 'POST',
                    url: $('#form').attr('action'),
                    data: {
                        product_id: product,
                        target: target,
                        items: data
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

            function get_data() {
                table
                    .rows()
                    .remove()
                    .draw();
                let product = $('#product_id').val()
                if (product == '' || product == null) {
                    return
                }
                $.get('{{ route('api.product.index') }}' + '/' + product).done(function(res) {
                    $('#target').val('')
                    if (res.data.sop != null) {
                        $('#target').val(res.data.sop.target || '1 Jam Menit')
                        table
                            .rows
                            .add(res.data.sop.items)
                            .draw();
                    }

                }).fail(function(xhr) {
                    console.log(xhr);
                })
            }

            $('#btn_import').click(function() {
                let imp = $('#import').val().trim();
                let rows = imp.split('\n');
                if (rows.length > 0 && rows[0] !== '') {
                    rows.forEach((row, index) => {
                        if (row) {
                            table.row.add({
                                item: row,
                            });
                        }
                    });

                    // Refresh tabel
                    table.draw();
                }
            });

            $('#btn_import_clear').click(function() {
                $('#import').val('')
            });

            $('#btn_target_clear').click(function() {
                $('#target').val('')
            });


            $('#table tbody').on('click', 'td:not(:first-child)', function() {
                let cell = table.cell(this);
                let oldValue = cell.data();

                // Cegah double input
                if ($(this).find('input').length > 0) return;

                // Ganti isi jadi input
                $(this).html(
                    `<input type="text" class="form-control edit-input" value="${oldValue||''}" />`);
                let input = $(this).find('input');
                input.focus();

                // Handle keluar dari input (blur)
                input.on('blur', function() {
                    let newValue = $(this).val().trim();

                    // Update value di tabel
                    cell.data(newValue).draw();
                });

                // Optional: tekan Enter untuk simpan
                input.on('keypress', function(e) {
                    if (e.which === 13) {
                        $(this).blur(); // trigger blur
                    }
                });
            });

        });
    </script>
@endpush
