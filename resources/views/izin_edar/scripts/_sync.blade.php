<script>
    // ── Sync Functions (Download Only) ────────────────────

    const SYNC_URL = '{{ route('api.izin_edars.sync') }}';
    const SYNC_PROGRESS_URL = '{{ route('api.izin_edars.sync_progress') }}';
    const SYNC_RESET_URL = '{{ route('api.izin_edars.sync_reset') }}';
    const CHECK_FILES_URL = '{{ route('api.izin_edars.check_files') }}';
    const SYNC_STOP_URL = '{{ route('api.izin_edars.sync_stop') }}';

    let syncPollInterval = null;

    const STATUS_LABELS = {
        'pending': 'Menunggu',
        'downloading': 'Mengunduh',
        'downloaded': 'Unduhan Selesai',
        'failed': 'Gagal',
        'idle': 'Tidak Aktif',
        'stopped': 'Dihentikan',
        'completed': 'Selesai',
    };

    const STATUS_COLORS = {
        'pending': 'secondary',
        'downloading': 'info',
        'downloaded': 'success',
        'failed': 'danger',
        'idle': 'secondary',
        'stopped': 'warning',
        'completed': 'success',
    };

    // ── Check if sync is running on page load ────────────

    $(document).ready(function() {
        $.ajax({
            url: SYNC_PROGRESS_URL,
            type: 'GET',
            isBlocking: false,
            success: function(log) {
                if (log && (log.status === 'pending' || log.status === 'downloading')) {
                    $('#syncCard').slideDown(200);
                    renderSyncProgress(log);
                    startPolling();
                }
            }
        });
    });

    // ── Trigger Sync (download from Kemkes) ──────────────

    function triggerSync() {
        checkSyncBeforeAction(function() {
            const $btn = $('#btnSync');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Starting...');

            $.ajax({
                url: SYNC_URL,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        $('#syncCard').slideDown(300);
                        startPolling();
                    } else {
                        if (res.can_stop) {
                            showSyncInProgressToast(res.message ||
                                'A sync is already in progress.');
                            $('#syncCard').slideDown(300);
                            startPolling();
                        } else {
                            show_message(res.message || 'Gagal memulai sync.', 'error');
                        }
                        resetSyncBtn();
                    }
                },
                error: function(xhr) {
                    const data = xhr.responseJSON || {};
                    if (xhr.status === 409 && data.can_stop) {
                        showSyncInProgressToast(data.message || 'A sync is already in progress.');
                        $('#syncCard').slideDown(300);
                        startPolling();
                    } else {
                        show_message(data.message || 'Gagal menghubungi server.', 'error');
                    }
                    resetSyncBtn();
                }
            });
        });
    }

    // ── Polling ──────────────────────────────────────────

    function startPolling() {
        if (syncPollInterval) clearInterval(syncPollInterval);
        syncPollInterval = setInterval(pollProgress, 10000);
        pollProgress();
    }

    function stopPolling() {
        if (syncPollInterval) {
            clearInterval(syncPollInterval);
            syncPollInterval = null;
        }
    }

    function pollProgress() {
        $.ajax({
            url: SYNC_PROGRESS_URL,
            type: 'GET',
            isBlocking: false,
            success: function(log) {
                renderSyncProgress(log);

                if (log.status === 'completed' || log.status === 'failed' || log.status === 'idle' || log
                    .status === 'stopped') {
                    stopPolling();
                    resetSyncBtn();
                }
            },
            error: function() {}
        });
    }

    // ── Render Sync Progress ─────────────────────────────

    function renderSyncProgress(log) {
        $('#syncCard').slideDown(200);

        const status = log.status || 'idle';
        const statusColor = STATUS_COLORS[status] || 'secondary';
        const statusLabel = STATUS_LABELS[status] || status;

        $('#syncStatusBadge')
            .removeClass('badge-secondary badge-info badge-primary badge-warning badge-success badge-danger')
            .addClass('badge-' + statusColor)
            .text(statusLabel);

        // Calculate overall progress based on completed downloads
        const cats = log.categories || {};
        const totalCats = Object.keys(cats).length;
        let doneCats = 0;
        for (const cat of Object.values(cats)) {
            if (['downloaded', 'failed'].includes(cat.status)) doneCats++;
        }
        const pct = totalCats > 0 ? Math.round((doneCats / totalCats) * 100) : 0;

        const isAnimating = (status === 'downloading');
        const barClass = isAnimating ? 'progress-bar-striped progress-bar-animated' : '';

        $('#syncProgressBar')
            .css('width', pct + '%')
            .attr('aria-valuenow', pct)
            .removeClass('progress-bar-striped progress-bar-animated')
            .addClass(barClass)
            .text(pct + '%');

        $('#syncProgressPct').text(pct + '%');
        $('#syncProgressText').text(
            totalCats > 0 ?
            `${statusLabel} — ${doneCats} / ${totalCats} file` :
            statusLabel
        );

        // Render per-category cards
        let catsHtml = '';
        for (const [name, cat] of Object.entries(cats)) {
            const catStatus = cat.status || 'pending';
            const catColor = STATUS_COLORS[catStatus] || 'secondary';
            const catLabel = STATUS_LABELS[catStatus] || catStatus;

            let sizeInfo = '';
            if (cat.size) {
                sizeInfo = `<br><small class="text-muted">${formatFileSize(cat.size)}</small>`;
            }

            catsHtml += `
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 shadow-sm" style="border-radius:12px;">
                        <div class="card-body p-3 text-center">
                            <h6 class="font-weight-bold mb-1">${name}</h6>
                            <span class="badge badge-${catColor} mb-2" style="font-size:0.75rem;">${catLabel}</span>
                            ${sizeInfo}
                            ${cat.error ? `<br><small class="text-danger" style="font-size:0.7rem;">${escapeHtml(cat.error.substring(0,80))}</small>` : ''}
                        </div>
                    </div>
                </div>`;
        }
        $('#syncCategories').html(catsHtml);

        if (log.error || log.message) {
            $('#syncMessage').show();
            $('#syncDetailMsg').text(log.error || log.message || '');
        } else {
            $('#syncMessage').hide();
        }

        if (log.is_running || log.can_stop) {
            $('#btnStopSync').show();
        } else {
            $('#btnStopSync').hide();
        }

        const showForceReset = (log.is_running || log.can_stop) ||
            (log.is_stale && ['pending', 'downloading'].includes(status)) ||
            status === 'failed' ||
            status === 'stopped';

        if (showForceReset) {
            if (!$('#btnForceReset').length) {
                $('#btnStopSync').after(
                    `<button id="btnForceReset" class="btn btn-sm btn-outline-danger" onclick="forceResetSync()" title="Force reset stuck sync">
                        <i class="fas fa-trash-alt mr-1"></i> Reset
                    </button>`
                );
            }
        } else {
            $('#btnForceReset').remove();
        }

        if (status === 'completed' || status === 'failed' || status === 'stopped') {
            setTimeout(function() {
                if (status === 'completed') {
                    $('#syncCard').slideUp(500);
                    $('#btnStopSync').hide();
                    $('#btnForceReset').remove();
                }
            }, 5000);
        }
    }

    function resetSyncBtn() {
        $('#btnSync').prop('disabled', false).html('<i class="fas fa-cloud-download-alt mr-1"></i> Sync Data');
    }

    // ── Utility: format file size ────────────────────────

    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '-';
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
    }

    // ── File Management URLs ──────────────────────────────

    const DOWNLOAD_FILE_URL = '{{ url('api') }}/izin-edars/files/';
    const DELETE_FILE_URL = '{{ url('api') }}/izin-edars/files/';

    // ── Check Files ──────────────────────────────────────

    function checkFiles() {
        const $btn = $('#btnCheckFiles');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Cek...');

        $.ajax({
            url: CHECK_FILES_URL,
            type: 'GET',
            success: function(res) {
                renderFileList(res.files, res.has_any);
                $btn.prop('disabled', false).html('<i class="fas fa-file-excel mr-1"></i> Cek File');
            },
            error: function() {
                show_message('Gagal mengecek file.', 'error');
                $btn.prop('disabled', false).html('<i class="fas fa-file-excel mr-1"></i> Cek File');
            }
        });
    }

    function renderFileList(files, hasAny) {
        const $list = $('#fileList');
        let html = '';

        const kelasMap = {
            'PKD': 'badge-pkd',
            'PKL': 'badge-pkl',
            'AKD': 'badge-akd',
            'AKL': 'badge-akl',
        };

        for (const [name, info] of Object.entries(files)) {
            const badgeClass = kelasMap[name] || 'badge-lainnya';
            const iconClass = info.exists ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
            const statusText = info.exists ? info.size_human : 'Tidak ada';
            const statusColor = info.exists ? 'text-success' : 'text-danger';

            let actionsHtml = '';
            if (info.exists) {
                actionsHtml = `
                    <div class="mt-2 d-flex justify-content-center" style="gap: 4px;">
                        <a href="${DOWNLOAD_FILE_URL}${name}/download"
                           class="btn btn-sm btn-outline-primary" title="Download file">
                            <i class="fas fa-download"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                            onclick="deleteFileKategori('${name}')" title="Hapus file">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>`;
            }

            html += `
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card border-0 shadow-sm" style="border-radius:12px;">
                        <div class="card-body p-3 text-center">
                            <h6 class="font-weight-bold mb-1">
                                <span class="badge-kategori ${badgeClass}">${name}</span>
                            </h6>
                            <i class="fas ${iconClass} fa-2x my-2"></i>
                            <p class="mb-1 small font-weight-bold ${statusColor}">${statusText}</p>
                            <small class="text-muted">${info.file}</small>
                            ${actionsHtml}
                        </div>
                    </div>
                </div>`;
        }

        $list.html(html);
        $('#fileCard').slideDown(200);
    }

    // ── Delete Excel File ────────────────────────────────

    function deleteFileKategori(kategori) {
        confirmation(`Hapus file <b>${kategori}.xlsx</b> dari storage?`, function(ok) {
            if (!ok) return;

            $.ajax({
                url: DELETE_FILE_URL + kategori,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    show_message(res.message || 'File berhasil dihapus.', 'success');
                    checkFiles(); // Refresh file list
                },
                error: function(xhr) {
                    const data = xhr.responseJSON || {};
                    show_message(data.message || 'Gagal menghapus file.', 'error');
                }
            });
        });
    }

    // ── Pre-check before action ──────────────────────────

    function checkSyncBeforeAction(callback) {
        $.ajax({
            url: SYNC_PROGRESS_URL,
            type: 'GET',
            isBlocking: false,
            success: function(log) {
                if (log && log.is_running) {
                    showSyncInProgressToast('A sync is already in progress.');
                    $('#syncCard').slideDown(300);
                    startPolling();
                } else {
                    callback();
                }
            },
            error: function() {
                callback();
            }
        });
    }

    // ── Force Reset / Stop ───────────────────────────────

    function forceResetSync() {
        confirmation('Force reset sync log? This will clear the stuck process and allow a new sync.', function(ok) {
            if (!ok) return;

            $.ajax({
                url: SYNC_RESET_URL,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    stopPolling();
                    $('#syncCard').slideUp(300);
                    resetSyncBtn();
                    loadData();
                },
                error: function() {
                    show_message('Gagal mereset sync log.', 'error');
                }
            });
        });
    }

    function stopSync() {
        confirmation('Batalkan proses download yang sedang berjalan?', function(ok) {
            if (!ok) return;

            $.ajax({
                url: SYNC_STOP_URL,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    stopPolling();
                    resetSyncBtn();
                    loadData();
                    $('#btnStopSync').hide();
                    $('#btnForceReset').hide();
                },
                error: function() {
                    show_message('Gagal menghentikan sync.', 'error');
                }
            });
        });
    }

    // ── Delete Data by Kategori ───────────────────────

    const DELETE_KATEGORI_URL = '{{ url('api') }}/izin-edars/kategori/';

    function deleteByKategori(kategori) {
        confirmation(`Hapus semua data kategori <b>${kategori}</b>? Tindakan ini tidak dapat dibatalkan.`, function(
            ok) {
            if (!ok) return;

            $.ajax({
                url: DELETE_KATEGORI_URL + kategori,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    show_message(res.message || 'Data berhasil dihapus.', 'success');
                    loadData();
                },
                error: function(xhr) {
                    const data = xhr.responseJSON || {};
                    show_message(data.message || 'Gagal menghapus data.', 'error');
                }
            });
        });
    }

    function deleteAllData() {
        confirmation('Hapus <b>SEMUA</b> data izin edar dari semua kategori? Tindakan ini tidak dapat dibatalkan.',
            function(ok) {
                if (!ok) return;

                const kategoris = ['PKD', 'PKL', 'AKD', 'AKL'];
                let completed = 0;
                let hasError = false;

                kategoris.forEach(function(kat) {
                    $.ajax({
                        url: DELETE_KATEGORI_URL + kat,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            completed++;
                            if (completed === kategoris.length) {
                                show_message('Semua data berhasil dihapus.', 'success');
                                loadData();
                            }
                        },
                        error: function() {
                            completed++;
                            hasError = true;
                            if (completed === kategoris.length) {
                                show_message('Sebagian data gagal dihapus.', 'error');
                                loadData();
                            }
                        }
                    });
                });
            });
    }

    // ── Sync In Progress Toast ───────────────────────────

    function showSyncInProgressToast(message) {
        iziToast.warning({
            title: 'Download Sedang Berjalan',
            message: message + '<br><small>Klik tombol di bawah untuk memaksa berhenti.</small>',
            position: 'topCenter',
            timeout: false,
            closeOnClick: false,
            drag: false,
            buttons: [
                ['<button><i class="fas fa-stop-circle mr-1"></i> Paksa Berhenti</button>', function(
                    instance, toast) {
                    instance.hide({
                        transitionOut: 'fadeOutUp'
                    }, toast, 'button');
                    forceStopSyncFromToast();
                }, true]
            ]
        });
    }

    function forceStopSyncFromToast() {
        $.ajax({
            url: SYNC_STOP_URL,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                $('.modal').modal('hide');
                stopPolling();
                $('#syncCard').slideUp(300);
                resetSyncBtn();
                loadData();
                show_message('Download berhasil dihentikan. Anda dapat memulai sync baru.', 'success');
            },
            error: function() {
                $.ajax({
                    url: SYNC_RESET_URL,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        $('.modal').modal('hide');
                        stopPolling();
                        $('#syncCard').slideUp(300);
                        resetSyncBtn();
                        loadData();
                        show_message('Sync telah di-reset paksa. Anda dapat memulai sync baru.',
                            'success');
                    },
                    error: function() {
                        show_message(
                            'Gagal menghentikan sync. Silakan coba lagi atau tunggu hingga selesai.',
                            'error');
                    }
                });
            }
        });
    }
</script>
