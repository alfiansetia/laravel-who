@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --success-soft: #ecfdf5;
            --success-text: #059669;
            --danger-soft: #fef2f2;
            --danger-text: #dc2626;
            --info-soft: #eff6ff;
            --info-text: #2563eb;
            --warning-soft: #fffbeb;
            --warning-text: #d97706;
            --secondary-soft: #f8fafc;
            --secondary-text: #64748b;
        }

        .content-header {
            padding: 1.5rem 0.5rem;
        }

        .stat-card {
            border-radius: 16px;
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .filter-card {
            border-radius: 16px;
            border: none;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .filter-card .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .table-card {
            border-radius: 16px;
            border: none;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* Modern Badges */
        .badge-soft {
            padding: 0.4em 0.8em;
            font-weight: 600;
            border-radius: 8px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
        }

        .badge-soft-success {
            background: var(--success-soft);
            color: var(--success-text);
        }

        .badge-soft-danger {
            background: var(--danger-soft);
            color: var(--danger-text);
        }

        .badge-soft-info {
            background: var(--info-soft);
            color: var(--info-text);
        }

        .badge-soft-warning {
            background: var(--warning-soft);
            color: var(--warning-text);
        }

        .badge-soft-secondary {
            background: var(--secondary-soft);
            color: var(--secondary-text);
        }

        #table th {
            background: #f8fafc;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            border-top: none;
            padding: 1rem;
        }

        #table td {
            padding: 1rem;
            vertical-align: middle !important;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
            margin: 0 2px;
        }

        .btn-edit {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            border: none;
        }

        .btn-edit:hover {
            background: #6366f1;
            color: #fff;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: none;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: #fff;
        }

        #table tbody tr:hover {
            background-color: #f8fafc;
        }

        .dt-buttons .btn {
            border-radius: 8px;
            margin-right: 5px;
            font-weight: 500;
        }

        .buttons-colvis {
            color: #475569 !important;
            background: #fff !important;
            border: 1px solid #e2e8f0 !important;
        }

        .buttons-colvis:hover {
            background: #f8fafc !important;
            color: #1e293b !important;
        }

        /* Hide elements for cleaner look */
        .dataTables_wrapper .dt--top-section {
            margin-bottom: 1rem;
            padding: 0 1.25rem;
        }

        .dataTables_wrapper .dt--bottom-section {
            padding: 1.25rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
                <div class="card stat-card h-100 p-3">
                    <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="text-secondary small font-weight-bold">TOTAL PROBLEMS</div>
                    <div class="h3 font-weight-bold mb-0 mt-1" id="stat-total">0</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4 mb-xl-0">
                <div class="card stat-card h-100 p-3">
                    <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="text-secondary small font-weight-bold">PENDING</div>
                    <div class="h3 font-weight-bold mb-0 mt-1 text-danger" id="stat-pending">0</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4 mb-md-0">
                <div class="card stat-card h-100 p-3">
                    <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="text-secondary small font-weight-bold">RESOLVED</div>
                    <div class="h3 font-weight-bold mb-0 mt-1 text-success" id="stat-done">0</div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card h-100 p-3">
                    <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="text-secondary small font-weight-bold">LAST UPDATE</div>
                    <div class="h6 font-weight-bold mb-0 mt-2 text-dark">{{ date('d M Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card filter-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="cursor: pointer;"
                data-toggle="collapse" data-target="#filterCollapse">
                <span><i class="fas fa-filter mr-2 text-primary"></i>Advance Filter</span>
                <div>
                    <button type="button" class="btn btn-sm btn-link text-secondary" id="btn_reset">
                        <i class="fas fa-undo mr-1"></i>Reset
                    </button>
                    <i class="fas fa-chevron-down ml-2 text-muted"
                        style="transform: rotate(-90deg); transition: transform 0.3s;"></i>
                </div>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form id="filter_form">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label>Type</label>
                                <select name="type" id="filter_type" class="form-control select2">
                                    <option value="">Semua Type</option>
                                    <option value="dus">Dus</option>
                                    <option value="unit">Unit</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label>Tahun</label>
                                <select name="year" id="filter_year" class="form-control select2">
                                    <option value="">Semua Tahun</option>
                                    @for ($y = date('Y'); $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label>Product</label>
                                <select name="product_id" id="filter_product" class="form-control select2">
                                    <option value="">Semua Product</option>
                                    @foreach ($products as $item)
                                        <option value="{{ $item->id }}">[{{ $item->code }}] {{ $item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label>Status</label>
                                <select name="status" id="filter_status" class="form-control select2">
                                    <option value="">Semua Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card table-card">
            <div class="py-3 px-4 d-flex justify-content-between align-items-center border-bottom bg-light">
                <h6 class="m-0 font-weight-bold text-dark">Daftar Problem</h6>
            </div>
            <table class="table table-hover mb-0" id="table" style="width: 100%; cursor: pointer;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">No</th>
                        <th>Number</th>
                        <th class="text-center">Date</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Status</th>
                        <th>PIC</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="detail-number">PRB-001</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="text-secondary small font-weight-bold">DATE</div>
                            <div class="text-dark" id="detail-date">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-secondary small font-weight-bold">TYPE</div>
                            <div id="detail-type">-</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-secondary small font-weight-bold">PIC</div>
                            <div class="text-dark" id="detail-pic">-</div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mb-3"><i class="fas fa-boxes mr-2 text-primary"></i>Problem Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="table-detail-items" style="width: 100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Lot</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary border-radius-8" data-dismiss="modal">Close</button>
                    <a href="#" id="detail-edit-link" class="btn btn-primary border-radius-8">
                        <i class="fas fa-edit mr-1"></i>Edit Data
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            var table = $('#table').DataTable({
                rowId: 'id',
                ajax: {
                    url: "{{ route('api.problem.index') }}",
                    data: function(d) {
                        d.type = $('#filter_type').val();
                        d.year = $('#filter_year').val();
                        d.product_id = $('#filter_product').val();
                        d.status = $('#filter_status').val();
                    },
                    dataSrc: function(json) {
                        // Calculate stats from data
                        let stats = {
                            total: json.data.length,
                            pending: 0,
                            done: 0
                        };
                        json.data.forEach(item => {
                            if (item.status == 'done') stats.done++;
                            else stats.pending++;
                        });

                        $('#stat-total').text(stats.total);
                        $('#stat-pending').text(stats.pending);
                        $('#stat-done').text(stats.done);

                        return json.data;
                    }
                },
                dom: "<'dt--top-section'<'row'<'col-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i><'dt--pagination'p>>",
                oLanguage: {
                    "sSearchPlaceholder": "Cari data...",
                    "sLengthMenu": "Hasil: _MENU_",
                },
                buttons: [{
                        text: '<i class="fa fa-plus mr-1"></i>Tambah Baru',
                        className: 'btn btn-primary',
                        action: function(e, dt, node, config) {
                            window.location.href = "{{ route('problems.create') }}"
                        }
                    },
                    {
                        extend: "colvis",
                        text: '<i class="fas fa-columns mr-1"></i>Columns',
                        className: 'btn buttons-colvis'
                    }
                ],
                order: [
                    [2, "desc"]
                ],
                columns: [{
                        data: 'id',
                        className: 'text-center',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    }, {
                        data: "number",
                        render: function(data) {
                            return `<span class="font-weight-bold text-dark">${data}</span>`;
                        }
                    },
                    {
                        data: "date",
                        className: 'text-center',
                        render: function(data) {
                            return `<span class="text-muted"><i class="far fa-calendar-alt mr-1"></i>${data}</span>`;
                        }
                    },
                    {
                        data: "type",
                        className: 'text-center',
                        render: function(data) {
                            let cls = data == 'dus' ? 'badge-soft-info' : 'badge-soft-warning';
                            return `<span class="badge badge-soft ${cls}">${data}</span>`;
                        }
                    },
                    {
                        data: "stock",
                        className: 'text-center',
                        render: function(data) {
                            let cls = data == 'stock' ? 'badge-soft-success' :
                                'badge-soft-secondary';
                            return `<span class="badge badge-soft ${cls}">${data}</span>`;
                        }
                    },
                    {
                        data: "status",
                        className: 'text-center',
                        render: function(data) {
                            let cls = data == 'done' ? 'badge-soft-success' : 'badge-soft-danger';
                            let icon = data == 'done' ? 'check' : 'clock';
                            return `<span class="badge badge-soft ${cls}"><i class="fas fa-${icon} mr-1"></i>${data}</span>`;
                        }
                    },
                    {
                        data: "pic",
                        render: function(data) {
                            return data ?
                                `<span><i class="far fa-user mr-1 text-muted"></i>${data}</span>` :
                                '-';
                        }
                    },
                    {
                        data: "id",
                        className: 'text-center',
                        orderable: false,
                        render: function(data) {
                            return `
                                <div class="d-flex justify-content-center">
                                    <a href="{{ url('problems') }}/${data}/edit" class="btn btn-action btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-action btn-delete" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>`;
                        }
                    },
                ],
            });

            // Filter triggers
            $('.select2').on('change', function() {
                table.ajax.reload();
            });

            // Reset button
            $('#btn_reset').click(function(e) {
                e.stopPropagation(); // Prevent card collapse when clicking reset
                $('#filter_type').val('').trigger('change');
                $('#filter_year').val('').trigger('change');
                $('#filter_product').val('').trigger('change');
                $('#filter_status').val('').trigger('change');
            });

            // Toggle icon rotation on collapse
            $('#filterCollapse').on('show.bs.collapse', function() {
                $('.filter-card .fa-chevron-down').css('transform', 'rotate(0deg)');
            }).on('hide.bs.collapse', function() {
                $('.filter-card .fa-chevron-down').css('transform', 'rotate(-90deg)');
            });

            var tableDetail = null;

            // Click row to show detail modal
            $('#table tbody').on('click', 'tr td:not(:last-child)', function() {
                let id = table.row(this).id();
                if (!id) return;

                // Load detail via AJAX
                $.ajax({
                    url: `{{ route('api.problem.index') }}/${id}`,
                    type: 'GET',
                    success: function(res) {
                        let data = res.data;
                        $('#detail-number').text(data.number);
                        $('#detail-date').text(data.date);
                        $('#detail-pic').text(data.pic || '-');

                        let typeCls = data.type == 'dus' ? 'badge-soft-info' :
                            'badge-soft-warning';
                        $('#detail-type').html(
                            `<span class="badge badge-soft ${typeCls}">${data.type}</span>`
                        );

                        // Destroy previous DT if exists
                        if (tableDetail) {
                            tableDetail.destroy();
                        }

                        // Populate secondary table
                        let itemsHtml = '';
                        if (data.items && data.items.length > 0) {
                            data.items.forEach(item => {
                                itemsHtml += `
                                    <tr>
                                        <td>
                                            <span class="font-weight-bold text-dark">[${item.product.code}]</span><br>
                                            <small class="text-muted">${item.product.name}</small>
                                        </td>
                                        <td class="text-center font-weight-bold">${item.qty}</td>
                                        <td class="text-center"><span class="badge badge-light border px-2">${item.lot || '-'}</span></td>
                                        <td>${item.desc || '-'}</td>
                                    </tr>
                                `;
                            });
                        }
                        $('#table-detail-items tbody').html(itemsHtml);

                        // Initialize DataTables for modal
                        tableDetail = $('#table-detail-items').DataTable({
                            dom: "<'row mb-2'<'col-sm-12 col-md-6'f><'col-sm-12 col-md-6 d-flex justify-content-end'B>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                            paging: true,
                            searching: true,
                            info: true,
                            lengthChange: false,
                            pageLength: 5,
                            buttons: [{
                                    extend: 'copy',
                                    className: 'btn btn-sm btn-info',
                                    text: '<i class="fas fa-copy"></i>'
                                },
                                {
                                    extend: 'excel',
                                    className: 'btn btn-sm btn-success',
                                    text: '<i class="fas fa-file-excel"></i>'
                                }
                            ]
                        });

                        // Set edit link
                        $('#detail-edit-link').attr('href',
                            `{{ url('problems') }}/${id}/edit`);

                        $('#modalDetail').modal('show');
                    },
                    error: function() {
                        show_message('Failed to load details', 'error');
                    }
                });
            });

            // Edit button click bypass - already handled by <a> tag in render
            // Delete action
            $('#table tbody').on('click', '.btn-delete', function(e) {
                e.stopPropagation();
                let id = table.row($(this).closest('tr')).id();
                confirmation('Yakin ingin menghapus data ini?', function(confirmed) {
                    if (confirmed) {
                        $.ajax({
                            url: `{{ url('problems') }}/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(result) {
                                show_message(result.message || 'Berhasil dihapus',
                                    'success');
                                table.ajax.reload();
                            },
                            error: function(xhr) {
                                show_message(xhr.responseJSON.message || 'Error!');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
