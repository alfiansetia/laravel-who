<script>
    $(document).ready(function() {
        const url_index = "{{ route('api.stock.index') }}";
        let currentRowId = 0;
        let currentCode = '';
        let currentName = '';

        // ── Main DataTable ───────────────────────────────
        const table = $('#table').DataTable({
            rowId: 'id',
            ajax: {
                url: url_index,
                data: function(dt) {
                    let loc = $('#location').val();
                    if (loc.length < 1) {
                        loc = ['center'];
                        $('#location').val('center').trigger('change');
                    }
                    dt['location[]'] = loc;
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message || 'Gagal memuat data stock!';
                    $('.dt-empty').text(message);
                    $('#table_processing').hide();
                    showToast(message, 'error');
                },
            },
            dom: "<'d-none'B>t<'stock-footer d-flex justify-content-between align-items-center py-2 px-3'lip>",
            paging: true,
            lengthChange: true,
            oLanguage: {
                "sSearchPlaceholder": "Cari kode atau nama...",
                "sLengthMenu": "Tampilkan _MENU_ data",
                "sInfo": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                "sInfoEmpty": "Tidak ada data",
                "sInfoFiltered": "(disaring dari _MAX_ total data)",
                "sZeroRecords": "Data tidak ditemukan",
                "sEmptyTable": "Tidak ada data tersedia",
            },
            lengthMenu: [
                [10, 25, 50, 100, -1],
                ['10', '25', '50', '100', 'Semua']
            ],
            order: [
                [0, 'asc']
            ],
            pageLength: 10,
            columns: [{
                data: "code",
                className: "text-left font-weight-bold",
            }, {
                data: "name",
                className: "text-left",
            }, {
                data: "quantity",
                className: 'text-center',
                render: function(data) {
                    let cls = parseInt(data) === 0 ? 'zero' : '';
                    return `<span class="qty-badge ${cls}">${parseInt(data).toLocaleString('id-ID')}</span>`;
                }
            }, {
                data: "akl",
                className: 'text-center',
                render: function(data) {
                    if (data) {
                        return `<span class="akl-badge has-akl" title="${escapeHtml(data)}">${escapeHtml(data)}</span>`;
                    }
                    return `<span class="akl-badge no-akl">-</span>`;
                }
            }, {
                data: "id",
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `<button type="button" class="btn btn-action btn-outline-primary btn-copy-row" data-code="${escapeHtml(row.code)}" data-name="${escapeHtml(row.name)}" title="Copy"><i class="fas fa-copy"></i></button>`;
                }
            }, ],
            createdRow: function(row, data) {
                $(row).attr('title', `${data.code} - ${data.name}`);
            },
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
                extend: 'excel',
                exportOptions: {
                    columns: ':visible'
                }
            }, {
                extend: 'pdf',
                exportOptions: {
                    columns: ':visible'
                }
            }, {
                extend: 'print',
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });


        // ── Search Input → DataTable Search ──────────────
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        // ── Export Dropdown Handlers ─────────────────────
        $('#exportCopy').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-copy').trigger();
        });
        $('#exportCsv').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-csv').trigger();
        });
        $('#exportExcel').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-excel').trigger();
        });
        $('#exportPdf').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-pdf').trigger();
        });
        $('#exportPrint').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-print').trigger();
        });

        // ── Row Click → Open Lot Modal ───────────────────
        $('#table tbody').on('click', 'tr', function() {
            const rowData = table.row(this).data();
            if (!rowData) return;

            currentRowId = table.row(this).id();
            currentCode = rowData.code;
            currentName = rowData.name;
            const qty = rowData.quantity;

            $('#modal_lotLabel').html(
                `<i class="fas fa-layer-group mr-2"></i>${escapeHtml(currentCode)} — ${escapeHtml(currentName)}`
            );

            let loc = $('#location').val();
            if (loc.length < 1) {
                loc = ['center'];
                $('#location').val('center').trigger('change');
            }

            // Destroy previous lot table if exists
            if ($.fn.DataTable.isDataTable('#table_lot')) {
                $('#table_lot').DataTable().clear().destroy();
            }

            $('#table_lot').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: `${url_index}/${currentRowId}`,
                    data: function(dt) {
                        let locations = $('#location').val();
                        if (locations.length < 1) {
                            locations = ['center'];
                        }
                        dt['location[]'] = locations;
                        dt['limit'] = qty;
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON?.message || 'Gagal memuat data lot!',
                            'error');
                    },
                },
                dom: "<'row mb-2'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center'f>>" +
                    "<'table-responsive'tr>" +
                    "<'row mt-2'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-md-end'p>>",
                oLanguage: {
                    "sSearchPlaceholder": "Cari lot...",
                    "sLengthMenu": "Tampilkan _MENU_",
                },
                paging: false,
                scrollCollapse: true,
                scrollY: '300px',
                columns: [{
                    data: "location",
                }, {
                    data: "lot",
                    className: 'text-center',
                    render: function(data, type, row) {
                        let text = data || '-';
                        if (row.expired && row.expired != 'False') {
                            text += ` / ${row.expired}`;
                        }
                        return text;
                    }
                }, {
                    data: "quantity",
                    className: 'text-center',
                    render: function(data) {
                        return `<span class="qty-badge">${parseInt(data).toLocaleString('id-ID')}</span>`;
                    }
                }],
                buttons: [{
                        extend: "colvis",
                        text: '<i class="fas fa-columns"></i>',
                        className: 'btn btn-sm btn-outline-primary',
                    },
                    {
                        extend: "collection",
                        text: '<i class="fas fa-download mr-1"></i>Export',
                        className: 'btn btn-sm btn-outline-primary',
                        buttons: [{
                            extend: 'copy',
                            title: function() {
                                return `[${currentCode}] - ${currentName}\n${new Date().toLocaleString('id-ID')}`;
                            },
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'csv',
                            title: function() {
                                return `[${currentCode}] - ${currentName} (${new Date().toLocaleString('id-ID')})`;
                            },
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'excel',
                            title: function() {
                                return `[${currentCode}] - ${currentName} (${new Date().toLocaleString('id-ID')})`;
                            },
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'pdf',
                            title: function() {
                                return `[${currentCode}] - ${currentName}\n${new Date().toLocaleString('id-ID')}`;
                            },
                            exportOptions: {
                                columns: ':visible'
                            }
                        }, {
                            extend: 'print',
                            title: function() {
                                return `[${currentCode}] - ${currentName}<br><small>${new Date().toLocaleString('id-ID')}</small>`;
                            },
                            exportOptions: {
                                columns: ':visible'
                            }
                        }],
                    }
                ],
                initComplete: function(settings, json) {
                    const data = json.data || [];
                    let lotParts = [];
                    let snParts = [];
                    let total = 0;

                    data.forEach(item => {
                        const lotLabel = item.lot || 'Tanpa Lot/Sn';
                        const expLabel = item.expired ? `/${item.expired}` : '';
                        lotParts.push(
                            `${lotLabel}${expLabel} = ${item.quantity} ea`);
                        snParts.push(lotLabel);
                        total += item.quantity;
                    });

                    $('#detail_lot').val(lotParts.join(', '));
                    $('#detail_sn').val(`${total} ea, SN : ` + snParts.join(', '));
                }
            });

            $('#modal_lot').modal('show');
        });

        // ── Opname Button ────────────────────────────────
        $('#btnOpname').on('click', function() {
            getOpname();
        });

        // ── Refresh Button ───────────────────────────────
        $('#refresh').on('click', function() {
            table.ajax.reload();
        });

        // ── Location Select2 ─────────────────────────────
        $('#location').select2({
            allowClear: false,
            placeholder: 'Pilih Lokasi...',
        }).on('change', function() {
            table.ajax.reload();
        });

        // ── Copy Row Button ──────────────────────────────
        $('#table tbody').on('click', '.btn-copy-row', function(e) {
            e.stopPropagation();
            const code = $(this).data('code');
            const name = $(this).data('name');
            copyToClipboard(`${code}\t${name}`);
        });

        // ── Copy Buttons ─────────────────────────────────
        $('#btn_copy_lot').on('click', function() {
            copyToClipboard($('#detail_lot').val());
        });

        $('#btn_copy_sn').on('click', function() {
            copyToClipboard($('#detail_sn').val());
        });

        // ── Helper Functions ─────────────────────────────

        function getOpname() {
            let locations = $('#location').val();
            if (!locations || locations.length === 0) {
                showToast('Pilih lokasi terlebih dahulu!', 'error');
                return;
            }
            window.location.href = "{{ route('api.stock.opname') }}" + "?location=" + locations.join(',');
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Berhasil disalin ke clipboard');
            }).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showToast('Berhasil disalin ke clipboard');
            });
        }

        function showToast(message, type) {
            let toast = document.querySelector('.toast-copy');
            if (!toast) {
                toast = document.createElement('div');
                toast.className = 'toast-copy';
                document.body.appendChild(toast);
            }
            const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
            toast.innerHTML = `<i class="fas ${icon} mr-2"></i>${message}`;
            toast.classList.add('show');
            clearTimeout(window._toastTimer);
            window._toastTimer = setTimeout(() => {
                toast.classList.remove('show');
            }, 2500);
        }

        // Expose for global use
        window.showToast = showToast;
    });
</script>
