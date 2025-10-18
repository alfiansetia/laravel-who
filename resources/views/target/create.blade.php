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

        <form method="POST" action="{{ route('pl.store') }}" id="form">
            @csrf
            <div class="card card-primary mt-3">
                <div class="card-header">
                    <h3 class="card-title">Manage SOP QC </h3>
                </div>

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
                        <div class="form-group col-12">
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

            <div class="card-footer">
                <a href="{{ route('target.index') }}" class="btn btn-secondary">Kembali</a>
                <a href="{{ route('target.create') }}" class="btn btn-warning">Refresh</a>
                <button type="submit" id="btn_simpan" class="btn btn-primary">Simpan</button>
            </div>
        </form>

        <div class="modal fade" id="modal_item" data-backdrop="static" data-keyboard="false"
            aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Add Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="item">ITEM</label>
                            <input name="item" type="text" class="form-control" id="item">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button id="btn_save_item" type="button" class="btn btn-primary">SAVE</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
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
                dom: "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                columns: [{
                    data: "item",
                    render: function() {
                        return '<span class="text-danger del-item"><i class="fas fa-trash"></i></span>'
                    }
                }, {
                    data: "item",
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

            $('#btn_save_item').click(function() {
                let item = $('#item').val()
                if (item == '' || item == null) {
                    alert('Item empty!')
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
                    alert('select Product!')
                    return
                }

                if (target == '' || target == null) {
                    alert('Target Empty!')
                    return
                }
                if (data.length < 1) {
                    alert('Item Empty!')
                    return
                }
                console.log(product);
                console.log(data);
                $.ajax({
                    type: 'POST',
                    url: `{{ route('api.target.index') }}`,
                    data: {
                        product: product,
                        target: target,
                        items: data
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        // table.ajax.reload()
                        alert('Success')
                    },
                    error: function(xhr, status, error) {
                        alert(xhr.responseJSON.message || 'Error!')
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
                    if (res.data.target != null) {
                        $('#target').val(res.data.target.target)
                        table
                            .rows
                            .add(res.data.target.items)
                            .draw();
                    }
                    console.log(res);

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
                $(this).html(`<input type="text" class="form-control edit-input" value="${oldValue}" />`);
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
