<script>
    $(document).ready(function() {
        const URL_INDEX_API = "{{ route('api.spreadsheet.index') }}";
        const URL_SYNC_PRODUCT_API = "{{ route('api.spreadsheet.sync_product') }}";
        const URL_COMPARE_PRODUCT_API = "{{ route('api.products.compare', ':product') }}";

        // ── Helper Functions ─────────────────────────────

        function parseDecimal(value, decimals = 2) {
            if (!value) return (0).toFixed(decimals);
            let num = value.toString().replace(',', '.');
            num = parseFloat(num);
            if (isNaN(num)) num = 0;
            return num.toFixed(decimals);
        }

        function renderPltbbCombined(row) {
            var p = row[8] ? String(row[8]).trim() : '';
            var l = row[9] ? String(row[9]).trim() : '';
            var t = row[10] ? String(row[10]).trim() : '';
            var b = row[11] ? String(row[11]).trim() : '';

            var isComplete = (p !== '' && p !== '0') &&
                (l !== '' && l !== '0') &&
                (t !== '' && t !== '0') &&
                (b !== '' && b !== '0');

            if (!p && !l && !t && !b) {
                return `<span class="pltbb-badge zero">-</span>`;
            }

            var text = `${p || '0'}x${l || '0'}x${t || '0'}/${b || '0'}`;
            var cls = isComplete ? '' : 'zero';
            return `<span class="pltbb-badge ${cls}" title="P:${p} L:${l} T:${t} B:${b}">${escapeHtml(text)}</span>`;
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
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

        // ── Main DataTable ───────────────────────────────
        // Columns: 0=Kode, 1=Name, 2=UOM, 3=PLTBB, 4=Note(hidden), 5=TglInput(hidden), 6=Status(hidden), 7=Aksi
        var table = $('#table').DataTable({
            rowId: 'id',
            ajax: {
                url: URL_INDEX_API,
                error: function(xhr, textStatus, errorThrown) {
                    let message = xhr.responseJSON?.message || 'Gagal memuat data!';
                    $('.dt-empty').text(message);
                    $('#table_processing').hide();
                    showToast(message, 'error');
                },
            },
            createdRow: function(row, data, dataIndex) {
                if (!data[8] || !data[9] || !data[10] || !data[11]) {
                    $(row).addClass('row-incomplete');
                }
            },
            dom: "<'d-none'B>t<'pltbb-footer d-flex justify-content-between align-items-center py-2 px-3'lip>",
            paging: true,
            lengthChange: true,
            info: false,
            oLanguage: {
                "sSearchPlaceholder": "Cari kode atau nama...",
                "sLengthMenu": "Tampilkan _MENU_ data",
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
                    data: "3",
                    className: "text-left font-weight-bold",
                }, {
                    data: "4",
                    className: "text-left",
                }, {
                    data: "6",
                    className: 'text-center',
                }, {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return renderPltbbCombined(row);
                    }
                }, {
                    data: "12",
                    className: 'text-left',
                    visible: false
                }, {
                    data: "13",
                    className: 'text-left',
                    visible: false
                }, {
                    data: null,
                    visible: false,
                    render: function(data, type, row) {
                        var p = row[8] ? String(row[8]).trim() : '';
                        var l = row[9] ? String(row[9]).trim() : '';
                        var t = row[10] ? String(row[10]).trim() : '';
                        var b = row[11] ? String(row[11]).trim() : '';

                        var isComplete = (p !== '' && p !== '0') &&
                            (l !== '' && l !== '0') &&
                            (t !== '' && t !== '0') &&
                            (b !== '' && b !== '0');

                        return isComplete ? 'lengkap' : 'tidak_lengkap';
                    }
                }, {
                    data: "3",
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                        <div class="d-inline-flex" style="gap: 4px;">
                            <button type="button" class="btn btn-action btn-warning btn-sync" title="Sync"><i class="fas fa-sync"></i></button>
                            <button type="button" class="btn btn-action btn-info btn-compare" title="Compare"><i class="fas fa-balance-scale"></i></button>
                        </div>`;
                    }
                }
            ],
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

        // ── Refresh Button ───────────────────────────────
        $('#refresh').on('click', function() {
            table.ajax.reload();
        });

        // ── Status Filter (column index 6 = Status) ─────
        $('#status_filter').on('change', function() {
            var val = $(this).val();
            if (val === 'all') {
                table.column(6).search('').draw();
            } else {
                table.column(6).search('^' + val + '$', true, false).draw();
            }
        });

        // ── Sync Row ─────────────────────────────────────
        $(document).on('click', '.btn-sync', function() {
            var row = table.row($(this).parents('tr')).data();
            let code = row[3];
            let p = row[8];
            let l = row[9];
            let t = row[10];
            let b = row[11];
            let note = row[12];
            if (!code || !p || !l || !t || !b) {
                showToast('Data tidak valid!', 'error');
                return;
            }
            confirmation(`Sync ${code} dengan Data Produk?`, function(confirm) {
                if (confirm) {
                    $.ajax({
                        url: URL_SYNC_PRODUCT_API,
                        type: 'POST',
                        data: {
                            code: code,
                            p: parseDecimal(p),
                            l: parseDecimal(l),
                            t: parseDecimal(t),
                            b: parseDecimal(b),
                            note: note
                        },
                        success: function(result) {
                            showToast(result.message, 'success');
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON.message || 'Error!', 'error');
                        }
                    })
                }
            })
        });

        // ── Compare Row ──────────────────────────────────
        $(document).on('click', '.btn-compare', function() {
            var row = table.row($(this).parents('tr')).data();
            let code = row[3];
            if (!code) {
                showToast('Data tidak valid!', 'error');
                return;
            }
            $.ajax({
                url: URL_COMPARE_PRODUCT_API.replace(':product', code),
                type: 'GET',
                success: function(result) {
                    $('#pltbb_p').text(row[8] || '-');
                    $('#pltbb_l').text(row[9] || '-');
                    $('#pltbb_t').text(row[10] || '-');
                    $('#pltbb_b').text(row[11] || '-');
                    $('#pltbb_note').text(row[12] || '-');
                    if (result.data.pltbb) {
                        $('#product_p').text(result.data.pltbb.p);
                        $('#product_l').text(result.data.pltbb.l);
                        $('#product_t').text(result.data.pltbb.t);
                        $('#product_b').text(result.data.pltbb.b);
                        $('#product_note').text(result.data.pltbb.note);
                    } else {
                        $('#product_p').text('-');
                        $('#product_l').text('-');
                        $('#product_t').text('-');
                        $('#product_b').text('-');
                        $('#product_note').text('-');
                    }
                    $('#modal_compare').modal('show');
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON.message || 'Error!', 'error');
                }
            })
        });

        // ── Sync All ─────────────────────────────────────
        $('#btnSyncAll').on('click', function() {
            confirmation(`Sync All Data dengan Data Produk?`, function(confirm) {
                if (confirm) {
                    $.ajax({
                        url: "{{ route('api.spreadsheet.sync_all') }}",
                        type: 'POST',
                        success: function(result) {
                            showToast(result.message, 'success');
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON.message || 'Error!', 'error');
                        }
                    })
                }
            })
        });

        // Expose for global use
        window.showToast = showToast;
    });
</script>
