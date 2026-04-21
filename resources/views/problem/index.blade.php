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

        /* Fix dropdown hidden in responsive table */
        .table-responsive {
            overflow: visible !important;
        }

        .table-card {
            overflow: visible !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        @include('problem.form_modal')
        @include('problem.modal')
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
                <div class="d-flex align-items-center">
                    <button type="button" id="btn_add_problem" class="btn btn-primary px-3">
                        <i class="fas fa-plus mr-1"></i>Tambah Problem
                    </button>
                </div>
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
                    extend: "colvis",
                    text: '<i class="fas fa-columns mr-1"></i>Columns',
                    className: 'btn buttons-colvis'
                }],
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
                        render: function(data, type, row) {
                            let statusBtn = row.status === 'done' ?
                                `<a class="dropdown-item btn-update-status" href="javascript:void(0)" data-id="${data}" data-status="pending"><i class="fas fa-clock mr-2 text-warning"></i>Mark as Pending</a>` :
                                `<a class="dropdown-item btn-update-status" href="javascript:void(0)" data-id="${data}" data-status="done"><i class="fas fa-check mr-2 text-success"></i>Mark as Done</a>`;

                            return `
                                <div class="dropdown text-center">
                                    <button class="btn btn-sm btn-light btn-round shadow-none" type="button" data-toggle="dropdown" data-boundary="viewport">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow border-0" style="border-radius: 10px;">
                                        ${statusBtn}
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item btn-duplicate" href="javascript:void(0)" data-id="${data}"><i class="far fa-copy mr-2 text-info"></i>Duplikasi</a>
                                        <a class="dropdown-item btn-edit-problem" href="javascript:void(0)" data-id="${data}"><i class="fas fa-edit mr-2 text-primary"></i>Edit</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item btn-delete text-danger" href="javascript:void(0)" data-id="${data}"><i class="fas fa-trash mr-2"></i>Hapus</a>
                                    </div>
                                </div>`;
                        }
                    },
                ],
            });

            // Filter triggers
            let isBatchUpdating = false;
            $('.select2').on('change', function() {
                if (!isBatchUpdating) {
                    table.ajax.reload();
                }
            });

            // Reset button
            $('#btn_reset').click(function(e) {
                e.stopPropagation();
                isBatchUpdating = true;
                $('#filter_type, #filter_year, #filter_product, #filter_status').val('').trigger('change');
                isBatchUpdating = false;
                table.ajax.reload();
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
                        $('#detail-edit-link').attr('data-id', id);

                        $('#modalDetail').modal('show');
                    },
                    error: function() {
                        show_message('Failed to load details', 'error');
                    }
                });
            });

            // --- PROBLEM FORM MODAL LOGIC ---
            var modalItems = [];
            var originalModalItems = [];
            var modalLogs = [];
            var originalModalLogs = [];
            var tableItems, tableLogs;
            var productMap = {};
            @foreach ($products as $p)
                productMap['{{ strtoupper($p->code) }}'] = {
                    id: '{{ $p->id }}',
                    code: '{{ $p->code }}',
                    name: '{{ addslashes($p->name) }}'
                };
            @endforeach

            $('.select2-modal').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalProblem')
            });
            $('.select2-inner').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modal_inner_item')
            });

            // Initialize Modal DataTables
            tableItems = $('#table_modal_items').DataTable({
                paging: false,
                searching: true,
                info: false,
                ordering: false,
                retrieve: true,
                dom: 'ft',
                language: {
                    search: ""
                },
            });
            $('#table_modal_items_filter input').addClass('form-control form-control-sm mb-2').attr('placeholder',
                'Cari item...');

            tableLogs = $('#table_modal_logs').DataTable({
                paging: false,
                searching: true,
                info: false,
                ordering: false,
                retrieve: true,
                dom: 'ft',
                language: {
                    search: ""
                },
            });
            $('#table_modal_logs_filter input').addClass('form-control form-control-sm mb-2').attr('placeholder',
                'Cari log...');

            $(".datepicker").flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true,
            });

            // OPEN CREATE MODAL
            $('#btn_add_problem').click(function() {
                modalItems = [];
                originalModalItems = [];
                modalLogs = [];
                originalModalLogs = [];
                renderModalItems();
                renderModalLogs();
                $('#form_problem')[0].reset();
                $('#form_method').val('POST');
                $('#problem_id').val('');
                $('#modalProblemTitle').html(
                    '<i class="fas fa-plus-circle mr-2 text-primary"></i>Tambah Problem Baru');

                // Set default date
                $('#prob_date').val(new Date().toISOString().split('T')[0]);

                // Fetch next number
                $.get("{{ route('api.problem.next_number') }}", function(res) {
                    $('#prob_number').val(res.number);
                    $('#modalProblem').modal('show');
                });
            });

            function openEditModal(id) {
                $.get(`{{ url('api/problem') }}/${id}`, function(res) {
                    let d = res.data;
                    $('#modalProblemTitle').html(
                        '<i class="fas fa-edit mr-2 text-primary"></i>Edit Problem');
                    $('#form_method').val('PUT');
                    $('#problem_id').val(d.id);
                    $('#prob_number').val(d.number);
                    $('#prob_date').val(d.date);
                    $('#prob_pic').val(d.pic).trigger('change');
                    $('#prob_ri_po').val(d.ri_po);
                    $('#prob_type').val(d.type).trigger('change');
                    $('#prob_stock').val(d.stock).trigger('change');
                    $('#prob_status').val(d.status).trigger('change');
                    $('#prob_email_on').val(d.email_on);

                    modalItems = d.items.map(i => ({
                        product_id: i.product_id,
                        qty: i.qty,
                        lot: i.lot,
                        desc: i.desc,
                        displayCode: i.product.code,
                        displayName: i.product.name
                    }));
                    originalModalItems = JSON.parse(JSON.stringify(modalItems));

                    modalLogs = d.logs.map(l => ({
                        date: l.date,
                        desc: l.desc
                    }));
                    originalModalLogs = JSON.parse(JSON.stringify(modalLogs));

                    renderModalItems();
                    renderModalLogs();
                    $('#modalProblem').modal('show');
                });
            }

            // OPEN EDIT MODAL CLICK
            $('#table tbody').on('click', '.btn-edit-problem', function(e) {
                e.stopPropagation();
                let id = table.row($(this).closest('tr')).id();
                openEditModal(id);
            });

            // EDIT FROM DETAIL MODAL
            $('#detail-edit-link').click(function(e) {
                e.preventDefault();
                let id = $(this).attr('data-id');
                $('#modalDetail').modal('hide');
                openEditModal(id);
            });

            // ITEM LOGIC
            var editingItemIndex = -1;

            $('#btn_modal_refresh_items').click(function() {
                modalItems = JSON.parse(JSON.stringify(originalModalItems));
                renderModalItems();
                show_message('Daftar item dikembalikan ke data awal', 'info');
            });

            $('#btn_modal_add_item').click(function() {
                editingItemIndex = -1;
                $('#modal_inner_item .modal-title').html('<i class="fas fa-plus mr-2"></i>Tambah Produk');
                $('#btn_modal_save_inner_item').text('Tambahkan');
                $('#modal_select_product').val('').trigger('change');
                $('#modal_item_qty').val(1);
                $('#modal_item_lot').val('');
                $('#modal_item_desc').val('');
                $('#modal_inner_item').modal('show');
            });

            $('#btn_modal_save_inner_item').click(function() {
                let pid = $('#modal_select_product').val();
                let sel = $('#modal_select_product').select2('data')[0];
                if (!pid) return show_message('Pilih produk!');

                let itemData = {
                    product_id: pid,
                    qty: $('#modal_item_qty').val(),
                    lot: $('#modal_item_lot').val(),
                    desc: $('#modal_item_desc').val(),
                    displayCode: sel.element.dataset.code,
                    displayName: sel.element.dataset.name
                };

                if (editingItemIndex > -1) {
                    modalItems[editingItemIndex] = itemData;
                } else {
                    modalItems.push(itemData);
                }

                renderModalItems();
                $('#modal_inner_item').modal('hide');
            });

            $(document).on('click', '.btn-modal-edit-item', function() {
                editingItemIndex = $(this).data('index');
                let item = modalItems[editingItemIndex];

                $('#modal_inner_item .modal-title').html('<i class="fas fa-edit mr-2"></i>Edit Produk');
                $('#btn_modal_save_inner_item').text('Simpan Perubahan');

                $('#modal_select_product').val(item.product_id).trigger('change');
                $('#modal_item_qty').val(item.qty);
                $('#modal_item_lot').val(item.lot);
                $('#modal_item_desc').val(item.desc);

                $('#modal_inner_item').modal('show');
            });

            $(document).on('click', '.btn-modal-remove-item', function() {
                modalItems.splice($(this).data('index'), 1);
                renderModalItems();
            });

            function renderModalItems() {
                tableItems.clear();
                modalItems.forEach((it, idx) => {
                    tableItems.row.add([
                        `<div class="text-center small">${idx+1}</div>`,
                        `<div><div class="font-weight-bold tiny">${it.displayCode}</div><div class="small text-muted" style="font-size:0.7rem">${it.displayName}</div>
                            <input type="hidden" name="items[${idx}][product_id]" value="${it.product_id}"></div>`,
                        `<div class="text-center">${it.qty}<input type="hidden" name="items[${idx}][qty]" value="${it.qty}"></div>`,
                        `<div class="small">${it.lot || '-'}<input type="hidden" name="items[${idx}][lot]" value="${it.lot || ''}"></div>`,
                        `<div class="small text-muted">${it.desc || '-'}<input type="hidden" name="items[${idx}][desc]" value="${it.desc || ''}"></div>`,
                        `<div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-xs btn-outline-warning btn-modal-edit-item mr-1" data-index="${idx}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-modal-remove-item" data-index="${idx}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>`
                    ]);
                });
                tableItems.draw();
            }

            // LOG LOGIC
            var editingLogIndex = -1;

            $('#btn_modal_refresh_logs').click(function() {
                modalLogs = JSON.parse(JSON.stringify(originalModalLogs));
                renderModalLogs();
                show_message('Daftar log dikembalikan', 'info');
            });

            $('#btn_modal_add_log').click(function() {
                editingLogIndex = -1;
                $('#modal_inner_log .modal-title').html('<i class="fas fa-history mr-2"></i>Tambah Log');
                $('#btn_modal_save_inner_log').text('Tambahkan');
                $('#modal_log_date').val(new Date().toISOString().split('T')[0]);
                $('#modal_log_desc').val('');
                $('#modal_inner_log').modal('show');
            });

            $('#btn_modal_save_inner_log').click(function() {
                let date = $('#modal_log_date').val();
                let desc = $('#modal_log_desc').val();
                if (!date || !desc) return show_message('Tanggal dan Keterangan wajib diisi!');

                let logData = {
                    date,
                    desc
                };

                if (editingLogIndex > -1) {
                    modalLogs[editingLogIndex] = logData;
                } else {
                    modalLogs.push(logData);
                }

                renderModalLogs();
                $('#modal_inner_log').modal('hide');
            });

            $(document).on('click', '.btn-modal-edit-log', function() {
                editingLogIndex = $(this).data('index');
                let log = modalLogs[editingLogIndex];
                $('#modal_inner_log .modal-title').html('<i class="fas fa-history mr-2"></i>Edit Log');
                $('#btn_modal_save_inner_log').text('Simpan Perubahan');
                $('#modal_log_date').val(log.date);
                $('#modal_log_desc').val(log.desc);
                $('#modal_inner_log').modal('show');
            });

            $(document).on('click', '.btn-modal-remove-log', function() {
                modalLogs.splice($(this).data('index'), 1);
                renderModalLogs();
            });

            function renderModalLogs() {
                tableLogs.clear();
                modalLogs.forEach((it, idx) => {
                    tableLogs.row.add([
                        `<div class="text-center small">${idx+1}</div>`,
                        `<div class="small font-weight-bold">${it.date}<input type="hidden" name="logs[${idx}][date]" value="${it.date}"></div>`,
                        `<div class="small text-muted">${it.desc}<input type="hidden" name="logs[${idx}][desc]" value="${it.desc}"></div>`,
                        `<div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-xs btn-outline-warning btn-modal-edit-log mr-1" data-index="${idx}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-modal-remove-log" data-index="${idx}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>`
                    ]);
                });
                tableLogs.draw();
            }

            // PASTE EXCEL LOGIC
            $('#btn_modal_paste_excel').click(() => {
                $('#modal_paste_area').val('');
                $('#modal_preview_container').hide();
                $('#modal_inner_paste').modal('show');
            });

            var parsedModalItems = [];
            var parsedGeneralInfo = {
                date: '',
                number: '',
                ri_po: '',
                stock: '',
                email_on: ''
            };

            $('#modal_paste_area').on('input', function() {
                let lines = $(this).val().trim().split('\n');
                let html = '';
                parsedModalItems = [];
                parsedGeneralInfo = {
                    date: '',
                    number: '',
                    ri_po: '',
                    stock: '',
                    email_on: ''
                };

                lines.forEach((line, idx) => {
                    if (!line.trim()) return;
                    let cols = line.split('\t');

                    // Capture general info from the first valid line
                    if (parsedGeneralInfo.date === '' && (cols[0] || cols[1] || cols[9] || cols[10])) {
                        if (cols[0]) parsedGeneralInfo.date = cols[0].trim();
                        if (cols[1]) parsedGeneralInfo.number = cols[1].trim();
                        if (cols[9]) parsedGeneralInfo.ri_po = cols[9].trim();
                        if (cols[10]) parsedGeneralInfo.email_on = cols[10].trim();
                        
                        // v di cols[6] = stock, v di cols[7] = import
                        if ((cols[6] || '').toLowerCase() === 'v') parsedGeneralInfo.stock = 'stock';
                        if ((cols[7] || '').toLowerCase() === 'v') parsedGeneralInfo.stock = 'import';
                    }

                    let code = (cols[2] || '').trim().toUpperCase();
                    let p = productMap[code] || null;
                    
                    // Qty is in cols[8]
                    let qty = parseInt(cols[8]) || 1;

                    parsedModalItems.push({
                        p,
                        lot: (cols[4] || '').trim(),
                        desc: (cols[5] || '').trim(),
                        qty: qty
                    });

                    let rowStock = '-';
                    if ((cols[6] || '').toLowerCase() === 'v') rowStock = 'Stock';
                    if ((cols[7] || '').toLowerCase() === 'v') rowStock = 'Import';

                    html += `<tr>
                        <td class="small text-muted">${cols[1] || '-'}</td>
                        <td><span class="font-weight-bold text-primary">${cols[2] || '-'}</span></td>
                        <td class="small">${cols[4] || '-'}</td>
                        <td class="small">${cols[9] || '-'}</td>
                        <td class="text-center font-weight-bold" style="color: #6366f1;">${rowStock}</td>
                        <td class="text-center">${p ? '✅' : '❌'}</td>
                    </tr>`;
                });

                $('#modal_preview_body').html(html);
                $('#modal_preview_container').show();
                $('#btn_modal_do_import').prop('disabled', parsedModalItems.filter(i => i.p).length === 0);
            });

            $('#btn_modal_do_import').click(function() {
                // Update general info from Excel if available
                if (parsedGeneralInfo.date) {
                    let parts = parsedGeneralInfo.date.split('/');
                    if (parts.length === 3) {
                        let isoDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                        $('#prob_date').val(isoDate).trigger('change');
                    } else {
                        $('#prob_date').val(parsedGeneralInfo.date).trigger('change');
                    }
                }
                if (parsedGeneralInfo.number) {
                    $('#prob_number').val(parsedGeneralInfo.number);
                }
                if (parsedGeneralInfo.ri_po) {
                    $('#prob_ri_po').val(parsedGeneralInfo.ri_po);
                }
                if (parsedGeneralInfo.stock) {
                    $('#prob_stock').val(parsedGeneralInfo.stock).trigger('change');
                }
                if (parsedGeneralInfo.email_on) {
                    let dEmail = parsedGeneralInfo.email_on;
                    let partsE = dEmail.split('/');
                    if (partsE.length === 3) {
                        let isoDateE = `${partsE[2]}-${partsE[1]}-${partsE[0]}`;
                        $('#prob_email_on').val(isoDateE).trigger('change');
                    } else {
                        $('#prob_email_on').val(dEmail).trigger('change');
                    }
                }

                parsedModalItems.filter(i => i.p).forEach(i => {
                    modalItems.push({
                        product_id: i.p.id,
                        qty: i.qty,
                        lot: i.lot,
                        desc: i.desc,
                        displayCode: i.p.code,
                        displayName: i.p.name
                    });
                });
                renderModalItems();
                $('#modal_inner_paste').modal('hide');
                show_message('Data berhasil diimpor & info diperbarui', 'info');
            });

            // SUBMIT LOGIC
            $('#form_problem').submit(function(e) {
                e.preventDefault();
                if (modalItems.length === 0) return show_message('Tambah minimal 1 item!', 'error');

                let id = $('#problem_id').val();
                let method = $('#form_method').val();
                let url = method === 'POST' ? "{{ route('api.problem.store') }}" :
                    `{{ url('api/problem') }}/${id}`;

                let formData = $(this).serialize();
                $('#btn_submit_problem').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...');

                $.ajax({
                    url,
                    type: 'POST',
                    data: formData
                }).done(res => {
                    show_message('Berhasil disimpan', 'success');
                    $('#modalProblem').modal('hide');
                    table.ajax.reload();
                }).fail(xhr => {
                    show_message(xhr.responseJSON.message || 'Error!', 'error');
                }).always(() => {
                    $('#btn_submit_problem').prop('disabled', false).html(
                        '<i class="fas fa-save mr-2"></i>Simpan Problem');
                });
            });

            // Status Update Logic
            $(document).on('click', '.btn-update-status', function() {
                let id = $(this).data('id');
                let status = $(this).data('status');
                let self = $(this);

                $.ajax({
                    url: `{{ url('api/problem') }}/${id}/status`,
                    type: 'POST',
                    data: {
                        status: status
                    }
                }).done(res => {
                    show_message(res.message, 'success');
                    table.ajax.reload(null, false); // Reload without resetting page
                }).fail(xhr => {
                    show_message(xhr.responseJSON.message || 'Error!', 'error');
                });
            });

            // DUPLICATE LOGIC
            $('#table tbody').on('click', '.btn-duplicate', function(e) {
                e.stopPropagation();
                let id = table.row($(this).closest('tr')).id();

                confirmation('Duplikasi data problem ini?', function(confirmed) {
                    if (confirmed) {
                        $.ajax({
                            url: `{{ url('api/problem') }}/${id}/duplicate`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(result) {
                                show_message(result.message, 'success');
                                table.ajax.reload(null, false);
                            },
                            error: function(xhr) {
                                show_message(xhr.responseJSON.message ||
                                    'Gagal menduplikasi',
                                    'error');
                            }
                        });
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
                            url: `{{ url('api/problem') }}/${id}`,
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
