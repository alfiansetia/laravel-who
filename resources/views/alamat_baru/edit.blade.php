@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
    <style>
        .koli-card {
            border: 2px solid #007bff;
            margin-bottom: 0px;
        }

        .koli-header {
            background-color: #90c0f3ff;
            color: white;
            padding: 2px 10px !important;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <div class="card card-primary mt-3 mb-3">
            <div class="card-header">
                <div class="row">
                    <div class="form-group col-md-6 mb-0">
                        <div class="input-group">
                            <input type="text" class="form-control" id="input_do" placeholder="CARI No DO"
                                value="{{ $data->do ?? 'CENT/OUT/' }}" autofocus>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="btn_get_do">
                                    <i class="fas fa-search mr-1"></i>GET
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-6 mb-0">
                        <select name="" id="select_do" class="form-control col-md-6 select2" style="width: 100%">
                            <option value="">Pilih</option>
                        </select>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('api.alamat_baru.update', $data->id) }}" id="form">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="tujuan">Tujuan</label>
                            <textarea name="tujuan" id="tujuan" class="form-control" placeholder="Tujuan" rows="4" required>{{ $data->tujuan }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="alamat">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" placeholder="Alamat" rows="4" required>{{ $data->alamat }}</textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="ekspedisi">Ekspedisi</label>
                            <input type="text" name="ekspedisi" id="ekspedisi" class="form-control"
                                placeholder="Ekspedisi" value="{{ $data->ekspedisi }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="total_koli">Total Koli (Manual)</label>
                            <input type="number" name="total_koli" id="total_koli" class="form-control"
                                placeholder="Total Koli" value="{{ $data->total_koli ?? 1 }}" min="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="up">UP</label>
                            <input type="text" name="up" id="up" class="form-control" placeholder="UP"
                                value="{{ $data->up }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tlp">Tlp</label>
                            <input type="text" name="tlp" id="tlp" class="form-control" placeholder="Tlp"
                                value="{{ $data->tlp }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="do">No DO</label>
                            <input type="text" name="do" id="do" class="form-control" placeholder="No DO"
                                value="{{ $data->do }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="epur">Epurchasing</label>
                            <input type="radio" name="ep" onclick="setEpur('Pembelian Offline')"> PO
                            <input type="radio" name="ep" onclick="setEpur('Pembelian Reguler')"> REG
                            <input type="radio" name="ep" checked onclick="setEpur('')"> NULL
                            <input type="text" name="epur" id="epur" class="form-control"
                                placeholder="Epurchasing" value="{{ $data->epur }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="untuk">Untuk</label>
                            <input type="text" name="untuk" id="untuk" class="form-control"
                                placeholder="Untuk" value="{{ $data->untuk }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="note">NOTE</label>
                            <textarea name="note" id="note" class="form-control" placeholder="note" rows="4" maxlength="250">{{ $data->note }}</textarea>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="note">NOTE WH</label>
                            <div class="text-danger font-weight-bold" id="n_t_wh"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="button" onclick="window.close()" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Tutup Tab
                    </button>
                    <a href="{{ route('alamat_baru.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                    <button type="button" id="add_koli" class="btn btn-info">
                        <i class="fas fa-plus mr-1"></i>Tambah Koli
                    </button>
                    <button type="submit" id="btn_simpan" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Simpan
                    </button>
                    <a href="{{ route('alamat_baru.show', $data->id) }}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-print mr-1"></i>Print All
                    </a>
                    <button type="button" id="btn_duplicate" class="btn btn-warning">
                        <i class="fas fa-clone mr-1"></i>Duplicate
                    </button>
                </div>
            </form>
        </div>

        {{-- Koli List --}}
        <div class="row" id="koli_container">
            <div class="col-12 col-xl-6" id="koli_left"></div>
            <div class="col-12 col-xl-6" id="koli_right"></div>
        </div>
    </div>
    @include('alamat_baru.modal')
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"
        integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        const URL_INDEX_API = "{{ route('api.alamat_baru.index') }}"
        const URL_INDEX = "{{ route('alamat_baru.index') }}"
        const CURRENT_ID = "{{ $data->id }}"
        const URL_KOLI_API = "{{ route('api.koli.index') }}"
        const URL_KOLI_ITEM_API = "{{ route('api.koli_item.index') }}"

        var currentKoliId = null;
        var currentItemId = null;

        function setEpur(val) {
            $('#epur').val(val)
        }

        $('.mask_angka').inputmask({
            alias: 'numeric',
            groupSeparator: '.',
            autoGroup: true,
            digits: 0,
            rightAlign: false,
            removeMaskOnSubmit: true,
            autoUnmask: true,
            min: 0,
        });

        $(document).ready(function() {
            // $.get("{{ route('api.products.index') }}").done(function(res) {
            //     for (let i = 0; i < res.data.length; i++) {
            //         let option = new Option(`[${res.data[i].code}] ${res.data[i].name || ''}`, res.data[i]
            //             .id,
            //             false, false);
            //         $('#select_product').append(option);
            //     }
            // });

            $('#btn_get_do').click(function() {
                let param = $('#input_do').val()
                $.get("{{ route('api.do.index') }}?search=" + param).done(function(res) {
                    $('#select_do').empty()
                    $('#select_do').append('<option value="">Pilih</option>');
                    let resData = res.data
                    for (let i = 0; i < resData.length; i++) {
                        let name = resData[i].name
                        if (resData[i].group_id != false) {
                            name += ' (' + resData[i].group_id[1] + ')'
                        }
                        if (resData[i].partner_id != false) {
                            name += ' ' + resData[i].partner_id[1]
                        }
                        let option = new Option(name, resData[i].id, true, true);
                        $('#select_do').append(option);
                    }
                    $('#select_do').val('')
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                });
            })

            $('#select_do').select2({
                theme: 'bootstrap4',
            }).on('change', function() {
                let data = $(this).select2('data');
                if (data[0].id == '') {
                    return;
                }
                let sid = data[0].id
                $.get("{{ url('api/do') }}/" + sid).done(function(res) {
                    let tujuan = ''
                    let ekspedisi = ''
                    let up = ''
                    let name = ''
                    let epur = ''
                    let note_to_wh = ''
                    if (res.data.partner_id != false) {
                        tujuan = res.data.partner_id[1]
                    }
                    if (res.data.ekspedisi_id != false) {
                        ekspedisi = res.data.ekspedisi_id[1]
                    }
                    if (res.data.delivery_manual != false) {
                        up = res.data.delivery_manual
                        if (up == '-') {
                            up = ''
                        }
                    }
                    if (res.data.name != false) {
                        name = res.data.name
                    }
                    if (res.data.no_aks != false) {
                        epur = res.data.no_aks
                        if (epur == '-') {
                            epur = ''
                        }
                    }
                    let alamat = res.data.partner_address
                    if (res.data.partner_address2 != false) {
                        alamat += '\n' + res.data.partner_address2
                    }
                    if (res.data.partner_address3 != false) {
                        alamat += '\n' + res.data.partner_address3
                    }
                    if (res.data.partner_address4 != false) {
                        alamat += '\n' + res.data.partner_address4
                    }

                    if (res.data.note_to_wh != false) {
                        note_to_wh = res.data.note_to_wh
                        note_to_wh = note_to_wh.replace(/\n/g, '<br>');
                    }

                    $('#do').val(name)
                    $('#up').val(up)
                    $('#alamat').val(alamat)
                    $('#tujuan').val(tujuan)
                    $('#ekspedisi').val(ekspedisi)
                    $('#epur').val(epur)
                    $('#n_t_wh').html(note_to_wh)
                    $('#tlp').val('')
                    $('#untuk').val('')
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                });

            });

            $('#select_product').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#item_modal"),
            })

            // Load kolis
            loadKolis();

            function loadKolis() {
                $.get(`${URL_KOLI_API}?alamat_baru_id=${CURRENT_ID}`).done(function(res) {
                    renderKolis(res.data);
                });
            }

            function renderKolis(kolis) {
                $('#koli_left, #koli_right').empty();
                if (kolis.length === 0) {
                    $('#koli_left').html(
                        '<p class="text-center">Belum ada koli. Klik "Tambah Koli" untuk menambahkan.</p>');
                    return;
                }

                kolis.forEach((koli, index) => {
                    let koliHtml = `
                        <div class="mb-2">
                            <form class="form-koli-inline" data-koli-id="${koli.id}">
                                <div class="koli-card" data-koli-id="${koli.id}">
                                    <div class="koli-header card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>
                                                <div class="d-flex align-items-center">
                                                    <div class="input-group input-group-sm mr-2" style="width: auto;">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><strong>Koli</strong></span>
                                                        </div>
                                                        <input type="text" class="form-control" name="urutan" id="urutan_${koli.id}" value="${koli.urutan}" style="width: 80px;" placeholder="1-7" required>
                                                    </div>
                                                    ${koli.nilai ? `<span class="badge badge-light" id="badge_nilai_${koli.id}">Rp. ${parseInt(koli.nilai).toLocaleString('id-ID')}</span>` : `<span class="badge badge-light" id="badge_nilai_${koli.id}" style="display:none;"></span>`}
                                                </div>
                                            </span>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-danger btn-delete-koli" data-koli-id="${koli.id}" title="Delete"><i class="fas fa-trash"></i></button>
                                                <button type="button" class="btn btn-sm btn-warning btn-sync-koli" data-koli-id="${koli.id}" title="Sync"><i class="fas fa-sync"></i></button>
                                                <button type="button" class="btn btn-sm btn-warning btn-duplicate-koli" data-koli-id="${koli.id}" title="Duplicate"><i class="fas fa-copy"></i></button>
                                                <button type="button" class="btn btn-sm btn-info btn-add-item" data-koli-id="${koli.id}" title="Add Item"><i class="fas fa-plus"></i></button>
                                                <button type="button" class="btn btn-sm btn-primary btn-print-koli" data-koli-id="${koli.id}" title="Print"><i class="fas fa-print"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-1">
                                        <table class="table table-sm table-bordered mb-1" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Product</th>
                                                    <th>Desc</th>
                                                    <th>Qty</th>
                                                    <th>Lot</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items_${koli.id}">
                                                ${renderItems(koli.items, koli.id)}
                                            </tbody>
                                        </table>
                                        <div class="mb-2">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">Rp.</span>
                                                </div>
                                                <input type="text" class="form-control mask_angka" name="nilai" id="nilai_${koli.id}" value="${koli.nilai || ''}" placeholder="Nilai Koli ${koli.urutan || ''}">
                                            </div>
                                        </div>
                                        <div class="form-group mb-0 d-flex flex-wrap justify-content-center">
                                            <div class="custom-control custom-checkbox mr-3">
                                                <input type="checkbox" class="custom-control-input" name="is_do" id="is_do_koli_${koli.id}" ${koli.is_do == 'yes' ? 'checked' : ''}>
                                                <label class="custom-control-label" for="is_do_koli_${koli.id}">SURAT JALAN/DO</label>
                                            </div>
                                            <div class="custom-control custom-checkbox mr-3">
                                                <input type="checkbox" class="custom-control-input" name="is_pk" id="is_pk_koli_${koli.id}" ${koli.is_pk == 'yes' ? 'checked' : ''}>
                                                <label class="custom-control-label" for="is_pk_koli_${koli.id}">PACKING KAYU</label>
                                            </div>
                                            <div class="custom-control custom-checkbox mr-3">
                                                <input type="checkbox" class="custom-control-input" name="is_asuransi" id="is_asuransi_koli_${koli.id}" ${koli.is_asuransi == 'yes' ? 'checked' : ''}>
                                                <label class="custom-control-label" for="is_asuransi_koli_${koli.id}">ASURANSI</label>
                                            </div>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="is_banting" id="is_banting_koli_${koli.id}" ${koli.is_banting == 'yes' ? 'checked' : ''}>
                                                <label class="custom-control-label" for="is_banting_koli_${koli.id}">JANGAN DIBANTING</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    `;
                    if (index % 2 === 0) {
                        $('#koli_left').append(koliHtml);
                    } else {
                        $('#koli_right').append(koliHtml);
                    }
                });

                // Attach event handlers
                attachKoliEventHandlers();
                attachFormHandlers();

                // Re-initialize input mask for nilai fields
                $('.mask_angka').inputmask({
                    alias: 'numeric',
                    groupSeparator: '.',
                    autoGroup: true,
                    digits: 0,
                    rightAlign: false,
                    removeMaskOnSubmit: true,
                    autoUnmask: true,
                    min: 0,
                });
            }

            function renderItems(items, koliId) {
                if (!items || items.length === 0) {
                    return '<tr><td colspan="6" class="text-center">Belum ada item</td></tr>';
                }

                return items.map((item, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>[${item.product.code}] ${item.product.name || ''}</td>
                        <td>${item.desc || ''}</td>
                        <td class="text-nowrap">${item.qty || ''}</td>
                        <td>${item.lot || ''}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" ${index > 0 ? '' : 'disabled'} class="btn btn-sm btn-secondary btn-item-up" data-item-id="${item.id}"><i class="fas fa-arrow-up"></i></button>
                                <button type="button" ${index < items.length - 1 ? '' : 'disabled'} class="btn btn-sm btn-secondary btn-item-down" data-item-id="${item.id}"><i class="fas fa-arrow-down"></i></button>
                                <button type="button" class="btn btn-sm btn-warning btn-edit-item" data-item-id="${item.id}" data-koli-id="${koliId}"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-item" data-item-id="${item.id}"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }

            function attachKoliEventHandlers() {
                // Delete koli
                $('.btn-delete-koli').click(function() {
                    let koliId = $(this).data('koli-id');
                    deleteKoli(koliId);
                });

                // Sync koli
                $('.btn-sync-koli').click(function() {
                    let koliId = $(this).data('koli-id');
                    syncKoli(koliId);
                });

                // Duplicate koli
                $('.btn-duplicate-koli').click(function() {
                    let koliId = $(this).data('koli-id');
                    duplicateKoli(koliId);
                });

                // Print koli
                $('.btn-print-koli').click(function() {
                    let koliId = $(this).data('koli-id');
                    printKoli(koliId);
                });

                // Add item
                $('.btn-add-item').click(function() {
                    currentKoliId = $(this).data('koli-id');
                    $('#item_modal').modal('show');
                });

                // Edit item
                $('.btn-edit-item').click(function() {
                    let itemId = $(this).data('item-id');
                    currentKoliId = $(this).data('koli-id');
                    editItem(itemId);
                });

                // Delete item
                $('.btn-delete-item').click(function() {
                    let itemId = $(this).data('item-id');
                    deleteItem(itemId);
                });

                // Item order
                $('.btn-item-up').click(function() {
                    orderItem($(this).data('item-id'), 'up');
                });
                $('.btn-item-down').click(function() {
                    orderItem($(this).data('item-id'), 'down');
                });
            }

            // Form submit handlers for inline editing
            function attachFormHandlers() {
                // Auto-submit on input blur (when user finishes typing and leaves field)
                $('.form-koli-inline input[type="text"]').off('change').on('change', function() {
                    $(this).closest('form').trigger('submit');
                });

                // Auto-submit on checkbox change
                $('.form-koli-inline input[type="checkbox"]').off('change').on('change', function() {
                    $(this).closest('form').trigger('submit');
                });

                // Handle form submission
                $('.form-koli-inline').off('submit').on('submit', function(e) {
                    e.preventDefault();

                    let koliId = $(this).data('koli-id');
                    let urutan = $(this).find('[name="urutan"]').val();
                    if (urutan == '') {
                        show_message('Koli harus diisi');
                        $('#urutan_' + koliId).focus();
                        return;
                    }
                    let formData = {
                        urutan: urutan,
                        nilai: $(this).find('[name="nilai"]').val(),
                        is_do: $(this).find('[name="is_do"]').prop('checked') ? 'yes' : 'no',
                        is_pk: $(this).find('[name="is_pk"]').prop('checked') ? 'yes' : 'no',
                        is_asuransi: $(this).find('[name="is_asuransi"]').prop('checked') ? 'yes' :
                            'no',
                        is_banting: $(this).find('[name="is_banting"]').prop('checked') ? 'yes' : 'no',
                    };

                    $.ajax({
                        type: 'PUT',
                        url: `${URL_KOLI_API}/${koliId}`,
                        data: formData
                    }).done(function(result) {
                        loadKolis();
                        show_message(result.message || 'Data koli berhasil disimpan!', 'success');
                    }).fail(function(xhr) {
                        show_message(xhr.responseJSON.message || 'Gagal menyimpan data koli!',
                            'error');
                    });
                });
            }

            // Add koli
            $('#add_koli').click(function() {
                $('#koli_modal').modal('show');
            });

            $('#form_add_koli').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: URL_KOLI_API,
                    data: {
                        alamat_baru_id: CURRENT_ID,
                        urutan: $('#urutan').val(),
                        nilai: $('#nilai_koli').val(),
                        is_do: $('#is_do_koli').prop('checked') ? 'yes' : 'no',
                        is_pk: $('#is_pk_koli').prop('checked') ? 'yes' : 'no',
                        is_asuransi: $('#is_asuransi_koli').prop('checked') ? 'yes' : 'no',
                        is_banting: $('#is_banting_koli').prop('checked') ? 'yes' : 'no',
                        is_last_koli: $('#is_last_koli_koli').prop('checked') ? 'yes' : 'no',
                    }
                }).done(function(result) {
                    $('#koli_modal').modal('hide');
                    $('#form_add_koli')[0].reset();
                    loadKolis();
                    show_message(result.message, 'success');
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
            });

            // Edit koli
            function editKoli(koliId) {
                $.get(`${URL_KOLI_API}/${koliId}`).done(function(res) {
                    let koli = res.data;
                    $('#urutan_edit').val(koli.urutan);
                    $('#nilai_koli_edit').val(koli.nilai);
                    $('#is_do_koli_edit').prop('checked', koli.is_do == 'yes');
                    $('#is_pk_koli_edit').prop('checked', koli.is_pk == 'yes');
                    $('#is_asuransi_koli_edit').prop('checked', koli.is_asuransi == 'yes');
                    $('#is_banting_koli_edit').prop('checked', koli.is_banting == 'yes');
                    $('#is_last_koli_koli_edit').prop('checked', koli.is_last_koli == 'yes');
                    $('#form_edit_koli').attr('action', `${URL_KOLI_API}/${koliId}`);
                    $('#edit_koli_modal').modal('show');
                });
            }

            $('#form_edit_koli').submit(function(e) {
                e.preventDefault();
                let url = $(this).attr('action');
                $.ajax({
                    type: 'PUT',
                    url: url,
                    data: {
                        urutan: $('#urutan_edit').val(),
                        nilai: $('#nilai_koli_edit').val(),
                        is_do: $('#is_do_koli_edit').prop('checked') ? 'yes' : 'no',
                        is_pk: $('#is_pk_koli_edit').prop('checked') ? 'yes' : 'no',
                        is_asuransi: $('#is_asuransi_koli_edit').prop('checked') ? 'yes' : 'no',
                        is_banting: $('#is_banting_koli_edit').prop('checked') ? 'yes' : 'no',
                        is_last_koli: $('#is_last_koli_koli_edit').prop('checked') ? 'yes' : 'no',
                    }
                }).done(function(result) {
                    $('#edit_koli_modal').modal('hide');
                    loadKolis();
                    show_message(result.message, 'success');
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
            });

            // Delete koli
            function deleteKoli(koliId) {
                confirmation('Delete Koli?', function(confirm) {
                    if (confirm) {
                        $.ajax({
                            type: 'DELETE',
                            url: `${URL_KOLI_API}/${koliId}`,
                        }).done(function(result) {
                            loadKolis();
                            show_message(result.message, 'success');
                        }).fail(function(xhr) {
                            show_message(xhr.responseJSON.message || 'Error!');
                        });
                    }
                });
            }

            // Add item
            $('#form_add_item').submit(function(e) {
                e.preventDefault();
                let selectedProduct = $('#select_product').select2('data');
                if (selectedProduct[0].id == '') {
                    show_message('Select Product!');
                    return;
                }
                $.ajax({
                    type: 'POST',
                    url: URL_KOLI_ITEM_API,
                    data: {
                        koli_id: currentKoliId,
                        product_id: selectedProduct[0].id,
                        qty: $('#qty_item').val(),
                        lot: $('#lot_item').val(),
                        desc: $('#desc_item').val(),
                    }
                }).done(function(result) {
                    $('#item_modal').modal('hide');
                    $('#form_add_item')[0].reset();
                    $('#select_product').val('').trigger('change');
                    loadKolis();
                    show_message(result.message, 'success');
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
            });

            // Edit item
            function editItem(itemId) {
                $.get(`${URL_KOLI_ITEM_API}/${itemId}`).done(function(res) {
                    let item = res.data;
                    $('#qty_item_edit').val(item.qty);
                    $('#lot_item_edit').val(item.lot);
                    $('#desc_item_edit').val(item.desc);
                    $('#form_edit_item').attr('action', `${URL_KOLI_ITEM_API}/${itemId}`);
                    $('#edit_item_modal').modal('show');
                });
            }

            $('#form_edit_item').submit(function(e) {
                e.preventDefault();
                let url = $(this).attr('action');
                $.ajax({
                    type: 'PUT',
                    url: url,
                    data: {
                        qty: $('#qty_item_edit').val(),
                        lot: $('#lot_item_edit').val(),
                        desc: $('#desc_item_edit').val(),
                    }
                }).done(function(result) {
                    $('#edit_item_modal').modal('hide');
                    loadKolis();
                    show_message(result.message, 'success');
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
            });

            // Delete item
            function deleteItem(itemId) {
                $.ajax({
                    type: 'DELETE',
                    url: `${URL_KOLI_ITEM_API}/${itemId}`,
                }).done(function(result) {
                    loadKolis();
                    show_message(result.message, 'success');
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
            }

            // Order item
            function orderItem(itemId, type) {
                $.ajax({
                    type: 'POST',
                    url: `${URL_KOLI_ITEM_API}/${itemId}/order`,
                    data: {
                        type: type
                    }
                }).done(function(result) {
                    loadKolis();
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!');
                });
            }

            // Submit form
            $('#form').submit(function(e) {
                e.preventDefault();
                let data = {
                    tujuan: $('#tujuan').val(),
                    alamat: $('#alamat').val(),
                    ekspedisi: $('#ekspedisi').val(),
                    total_koli: $('#total_koli').val(),
                    up: $('#up').val(),
                    tlp: $('#tlp').val(),
                    do: $('#do').val(),
                    epur: $('#epur').val(),
                    untuk: $('#untuk').val(),
                    note: $('#note').val(),
                }
                $.ajax({
                    type: 'PUT',
                    url: `${URL_INDEX_API}/${CURRENT_ID}`,
                    data: data,
                    beforeSend: function() {},
                    success: function(res) {
                        show_message(res.message || 'Success!', 'success')
                        // window.open(`${URL_INDEX}/${CURRENT_ID}`, '_blank')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!', 'error')
                    }
                });
            })

            // Duplicate
            $('#btn_duplicate').click(function() {
                $.ajax({
                    type: 'POST',
                    url: `${URL_INDEX_API}/${CURRENT_ID}/duplicate`,
                    data: {}
                }).done(function(result) {
                    window.open(`${URL_INDEX}/${result.data.id}/edit`)
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                })
            })

            function syncKoli(koli_id) {
                $.ajax({
                    type: 'GET',
                    url: `${URL_KOLI_API}/${koli_id}/sync`,
                }).done(function(result) {
                    loadKolis()
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                })
            }

            function duplicateKoli(koli_id) {
                $.ajax({
                    type: 'POST',
                    url: `${URL_KOLI_API}/${koli_id}/duplicate`,
                    data: {}
                }).done(function(result) {
                    loadKolis()
                }).fail(function(xhr) {
                    show_message(xhr.responseJSON.message || 'Error!')
                })
            }

            function printKoli(koli_id) {
                window.open(`${URL_INDEX}/${CURRENT_ID}?koli_id=${koli_id}`, '_blank')
            }

            $('#koli_modal').on('shown.bs.modal', function() {
                $('#urutan').focus();
            });

            $('#edit_koli_modal').on('shown.bs.modal', function() {
                $('#urutan_edit').focus();
            });

            $('#item_modal').on('shown.bs.modal', function() {
                $('#qty_item').focus();
            });

            $('#edit_item_modal').on('shown.bs.modal', function() {
                $('#qty_item_edit').focus();
            });

        });
    </script>
@endpush
