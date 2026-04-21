<!-- Modal Add/Edit Problem -->
<div class="modal fade" id="modalProblem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="modalProblemTitle">
                    <i class="fas fa-plus-circle mr-2 text-primary"></i>Tambah Problem Baru
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_problem">
                @csrf
                <input type="hidden" name="_method" id="form_method" value="POST">
                <input type="hidden" id="problem_id">

                <div class="modal-body pt-3">
                    <!-- Main Info Section -->
                    <div class="p-3 rounded-lg border mb-4" style="background: #fcfcfd;">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Nomor Problem</label>
                                <input type="text" name="number" id="prob_number"
                                    class="form-control form-control-sm" required placeholder="Contoh: 2026-APR-001">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Tanggal</label>
                                <input type="text" name="date" id="prob_date"
                                    class="form-control form-control-sm datepicker" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">PIC</label>
                                <select name="pic" id="prob_pic"
                                    class="form-control form-control-sm select2-modal">
                                    <option value="Karim">Karim</option>
                                    <option value="Sofyan">Sofyan</option>
                                    <option value="Asep">Asep</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">RI / PO</label>
                                <input type="text" name="ri_po" id="prob_ri_po"
                                    class="form-control form-control-sm" placeholder="RI-xxxx">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Type</label>
                                <select name="type" id="prob_type"
                                    class="form-control form-control-sm select2-modal">
                                    <option value="unit">Unit</option>
                                    <option value="dus">Dus</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Stock</label>
                                <select name="stock" id="prob_stock"
                                    class="form-control form-control-sm select2-modal">
                                    <option value="stock">Stock</option>
                                    <option value="import">Import</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Status</label>
                                <select name="status" id="prob_status"
                                    class="form-control form-control-sm select2-modal">
                                    <option value="pending">Pending</option>
                                    <option value="done">Done</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Email On</label>
                                <input type="text" name="email_on" id="prob_email_on"
                                    class="form-control form-control-sm datepicker">
                            </div>
                        </div>
                    </div>

                    <!-- Items Section -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="section-title m-0">
                            Produk Bermasalah
                            <button type="button" class="btn btn-link btn-sm text-muted p-0 ml-2" id="btn_modal_refresh_items" title="Reset/Sinkron Ulang">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-info mr-2" id="btn_modal_paste_excel">
                                <i class="fas fa-paste mr-1"></i>Paste Excel
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" id="btn_modal_add_item">
                                <i class="fas fa-plus mr-1"></i>Tambah Produk
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive bg-white rounded border mb-4" style="max-height: 250px;">
                        <table class="table table-hover table-items mb-0" id="table_modal_items">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>Product</th>
                                    <th class="text-center" style="width: 80px;">Qty</th>
                                    <th>Lot / SN</th>
                                    <th>Description</th>
                                    <th style="width: 80px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="modal_items_body">
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Belum ada item ditambahkan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Logs Section -->
                    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                        <div class="section-title m-0">
                            Log Aktivitas
                            <button type="button" class="btn btn-link btn-sm text-muted p-0 ml-2" id="btn_modal_refresh_logs" title="Reset Log">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn_modal_add_log">
                            <i class="fas fa-plus mr-1"></i>Tambah Log
                        </button>
                    </div>
                    <div class="table-responsive bg-white rounded border" style="max-height: 200px;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 50px;">#</th>
                                    <th style="width: 120px;">Tanggal</th>
                                    <th>Keterangan / Progress</th>
                                    <th class="text-center" style="width: 80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="modal_logs_body">
                                <tr><td colspan="4" class="text-center py-4 text-muted small">Belum ada log aktivitas</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light-soft"
                    style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                    <button type="button" class="btn btn-light px-4" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5" id="btn_submit_problem">
                        <i class="fas fa-save mr-2"></i>Simpan Problem
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Inner Modal for Adding Single Item -->
<div class="modal fade" id="modal_inner_item" tabindex="-1" aria-hidden="true"
    style="background: rgba(0,0,0,0.2); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-plus mr-2"></i>Tambah Produk</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="small font-weight-bold">Pilih Produk <span class="text-danger">*</span></label>
                    <select id="modal_select_product" class="form-control select2-inner">
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" data-code="{{ $p->code }}"
                                data-name="{{ $p->name }}">
                                [{{ $p->code }}] {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="row mt-3">
                    <div class="col-6">
                        <label class="small font-weight-bold">Qty <span class="text-danger">*</span></label>
                        <input type="number" id="modal_item_qty" class="form-control" value="1"
                            min="1">
                    </div>
                    <div class="col-6">
                        <label class="small font-weight-bold">Lot / SN</label>
                        <input type="text" id="modal_item_lot" class="form-control" placeholder="Lot...">
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label class="small font-weight-bold">Keterangan / Defect</label>
                    <textarea id="modal_item_desc" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4" id="btn_modal_save_inner_item">Tambahkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Inner Modal Paste Excel -->
<div class="modal fade" id="modal_inner_paste" tabindex="-1" aria-hidden="true"
    style="background: rgba(0,0,0,0.2); z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-paste mr-2"></i>Paste dari Excel</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3">
                    Format: <strong>Item No (TAB) Description (TAB) Serial (TAB) QC Result (TAB) QTY</strong>
                </div>
                <textarea id="modal_paste_area" class="form-control" rows="6" placeholder="Paste baris Excel di sini..."
                    style="background: #f8fafc; font-size: 0.8rem;"></textarea>

                <div id="modal_preview_container" class="mt-3" style="display:none;">
                    <label class="small font-weight-bold mb-1">Preview Data</label>
                    <div class="table-responsive" style="max-height: 200px;">
                        <table class="table table-sm table-bordered" style="font-size: 0.75rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Code</th>
                                    <th>SN</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="modal_preview_body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btn_modal_do_import" disabled>Impor Data</button>
            </div>
        </div>
    </div>
</div>

<!-- Inner Modal for Adding Single Log -->
<div class="modal fade" id="modal_inner_log" tabindex="-1" aria-hidden="true"
    style="background: rgba(0,0,0,0.2); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header border-0 bg-info text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h6 class="modal-title font-weight-bold"><i class="fas fa-history mr-2"></i>Tambah Log Aktivitas</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div class="form-group mb-3">
                    <label class="small font-weight-bold">Tanggal Log <span class="text-danger">*</span></label>
                    <input type="text" id="modal_log_date" class="form-control datepicker" required>
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Keterangan / Progress <span class="text-danger">*</span></label>
                    <textarea id="modal_log_desc" class="form-control" rows="4" placeholder="Tulis progress di sini..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-info text-white px-4" id="btn_modal_save_inner_log">Tambahkan</button>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light-soft {
        background-color: #f8f9fa;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(1.5em + 0.5rem + 2px) !important;
        font-size: 0.875rem !important;
    }

    /* Fix nested Z-index if needed */
    .modal-backdrop+.modal-backdrop {
        z-index: 1055;
    }
</style>
