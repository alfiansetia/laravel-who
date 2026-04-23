@extends('template')

@section('content')
    <div class="container-fluid px-4 py-3">
        <!-- Header with Gradient Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between p-4 rounded-lg shadow-sm border-0"
                    style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); color: white;">
                    <div>
                        <h2 class="font-weight-bold mb-1 ml-2"><i class="fas fa-folder-open mr-2 text-warning"></i> File
                            Search Manager</h2>
                        <p class="mb-0 opacity-75 ml-2 font-weight-light">Cari dan jelajahi struktur data file secara
                            internal.</p>
                        <div class="d-flex flex-wrap mt-3 ml-2" style="gap: 10px;">
                            <span class="badge btn-white-soft px-3 py-2 font-weight-light">
                                <i class="fas fa-clock mr-1 opacity-75"></i> Update: <span
                                    id="headerLastUpdated">{{ $lastUpdated ?? 'Never' }}</span>
                            </span>
                            <span class="badge btn-white-soft px-3 py-2 font-weight-light">
                                <i class="fas fa-database mr-1 opacity-75"></i> <span id="headerTotalStats">0 folders, 0
                                    files</span>
                            </span>
                        </div>
                    </div>
                    <div class="d-none d-md-block mr-3">
                        <a href="{{ route('tools.file_search.download_script') }}"
                            class="btn btn-white-soft shadow-sm font-weight-bold px-3 rounded-pill mr-2">
                            <i class="fas fa-download mr-2"></i> Script
                        </a>
                        <button class="btn btn-light shadow-sm font-weight-bold px-4 rounded-pill" data-toggle="modal"
                            data-target="#uploadModal">
                            <i class="fas fa-upload mr-2 text-primary"></i> Update Index
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row">
            <!-- Main Search Card -->
            <div class="col-12">
                <div class="card border-0 shadow-lg overflow-hidden">
                    <div class="card-header bg-white py-4 border-bottom">
                        <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0 px-4">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                            </div>
                            <input type="search" id="searchInput" class="form-control border-0 px-2"
                                placeholder="Ketik nama file atau folder untuk mencari..." autofocus>
                            <div class="input-group-append">
                                <span class="input-group-text bg-light border-0 px-3 text-muted" id="resultsCount">
                                    0 items found
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 px-2 d-flex align-items-center flex-wrap" style="gap: 20px;">
                            <div class="d-flex align-items-center">
                                <span class="small text-muted mr-2 font-weight-bold">Type:</span>
                                <div class="btn-group btn-group-sm shadow-sm rounded overflow-hidden" role="group">
                                    <button type="button" class="btn btn-light border filter-type px-3 active"
                                        data-type="all">All</button>
                                    <button type="button" class="btn btn-light border filter-type px-3"
                                        data-type="folder">Folders</button>
                                    <button type="button" class="btn btn-light border filter-type px-3"
                                        data-type="file">Files</button>
                                </div>
                            </div>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="caseSensitiveSwitch">
                                <label class="custom-control-label small text-muted cursor-pointer font-weight-bold"
                                    for="caseSensitiveSwitch">Case Sensitive</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="px-4 py-3 bg-light border-bottom d-flex align-items-center justify-content-between"
                            id="navigationHeader">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 m-0" id="breadcrumbList">
                                    <li class="breadcrumb-item active"><i class="fas fa-home"></i> Root</li>
                                </ol>
                            </nav>
                            <small class="text-muted" id="viewModeBadge">Browse Mode</small>
                        </div>
                        <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
                            <table class="table table-hover mb-0" id="fileTable">
                                <thead class="bg-light text-muted sticky-top" style="top: 0; z-index: 10;">
                                    <tr>
                                        <th class="px-4 border-0">Name</th>
                                        <th class="border-0">Type</th>
                                        <th class="border-0 text-right pr-4">Last Modified</th>
                                    </tr>
                                </thead>
                                <tbody id="fileListBody">
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="spinner-border text-primary speed-slow" role="status">
                                                <span class="sr-only">Loading content...</span>
                                            </div>
                                            <div class="mt-2 text-muted">Memuat data file...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-clock mr-1"></i> Last updated: {{ $lastUpdated ?? 'Never' }}
                        </small>
                        <div class="text-muted small" id="footerTotalInfo">
                            Total: <span class="font-weight-bold" id="footerTotalCount">0</span> items in index
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title font-weight-bold">Update File Index</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form id="uploadForm" action="{{ route('tools.file_search.upload') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4 text-center">
                        <div id="uploadStatusUI">
                            <div class="alert alert-info border-0 shadow-none mb-4 text-left"
                                style="background-color: #f0f7ff;">
                                <small>
                                    <i class="fas fa-info-circle mr-1"></i> Gunakan script
                                    <code>deployment/generate-index.bat</code> untuk menghasilkan file JSON terbaru, lalu
                                    upload
                                    di sini.
                                </small>
                            </div>
                            <div class="form-group mb-0 text-left">
                                <label class="font-weight-bold">Pilih File JSON</label>
                                <div class="custom-file">
                                    <input type="file" name="json_file" class="custom-file-input" id="jsonFile"
                                        accept=".json" required>
                                    <label class="custom-file-label" for="jsonFile">Pilih file...</label>
                                </div>
                            </div>
                        </div>

                        <div id="loadingUI" style="display: none;">
                            <div class="py-4">
                                <div class="spinner-border text-primary speed-slow mb-3"
                                    style="width: 3rem; height: 3rem;" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <h6 class="font-weight-bold">Memproses File Index...</h6>
                                <p class="text-muted small">Harap tunggu sebentar...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 shadow" id="submitBtn">
                            <span class="btn-text">Upload & Update</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('css')
        <style>
            .badge-soft-warning {
                background-color: #fff8eb;
                color: #ff9800;
            }

            .badge-soft-info {
                background-color: #e0faff;
                color: #00bcd4;
            }

            .btn-white-soft {
                background-color: rgba(255, 255, 255, 0.15);
                color: white;
                border: 1px solid rgba(255, 255, 255, 0.3);
                transition: all 0.2s;
            }

            .btn-white-soft:hover {
                background-color: rgba(255, 255, 255, 0.25);
                color: white;
                color: white;
                transform: translateY(-1px);
            }

            .badge.btn-white-soft {
                font-size: 0.85rem;
                display: flex;
                align-items: center;
                border-radius: 8px;
            }

            .file-item {
                transition: all 0.2s;
                cursor: default;
            }

            .file-item:hover {
                background-color: #f8fafc;
            }

            .file-name {
                font-size: 0.95rem;
                color: #334155;
            }

            .form-control:focus {
                box-shadow: none;
            }

            .rounded-lg {
                border-radius: 12px;
            }

            .sticky-top {
                transition: all 0.3s;
            }

            .file-item[data-type="folder"] {
                cursor: pointer !important;
            }

            .file-item[data-type="folder"]:hover .file-name {
                color: #6366f1;
                text-decoration: underline;
            }

            .breadcrumb-item+.breadcrumb-item::before {
                content: "›";
                color: #cbd5e1;
            }

            .breadcrumb-item a {
                color: #6366f1;
                font-weight: 500;
                text-decoration: none;
            }

            .breadcrumb-item a:hover {
                text-decoration: underline;
            }

            .filter-type.active {
                background-color: #6366f1 !important;
                color: white !important;
                border-color: #6366f1 !important;
            }

            .cursor-pointer {
                cursor: pointer;
            }
        </style>
    @endpush

    @push('js')
        <script>
            let allFileData = [];
            let currentPath = ''; // Root is empty string

            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const fileListBody = document.getElementById('fileListBody');
                const resultsCountStr = document.getElementById('resultsCount');
                const footerTotalCount = document.getElementById('footerTotalCount');
                const breadcrumbList = document.getElementById('breadcrumbList');
                const viewModeBadge = document.getElementById('viewModeBadge');

                // Initial fetch
                fetchData();

                function fetchData() {
                    $.get('{{ route('tools.file_search.data') }}', function(data) {
                        allFileData = data;
                        renderUI(); // Initial render

                        const foldersTotal = allFileData.filter(f => f.type === 'folder').length;
                        const filesTotal = allFileData.filter(f => f.type === 'file').length;

                        // Update Header Stats
                        document.getElementById('headerTotalStats').textContent =
                            `${foldersTotal} folders, ${filesTotal} files`;

                        // Update Footer Stats
                        document.getElementById('footerTotalInfo').innerHTML = `
                            Total: <span class="font-weight-bold">${allFileData.length}</span> items 
                            <small>(${foldersTotal} folders, ${filesTotal} files)</small>`;
                    }).fail(function() {
                        fileListBody.innerHTML = `
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-database fa-3x mb-3 opacity-25"></i>
                                        <h5>Gagal memuat data</h5>
                                        <p>Silakan upload file <code>file-index.json</code> atau periksa koneksi.</p>
                                    </div>
                                </td>
                            </tr>`;
                    });
                }

                function renderUI() {
                    const query = searchInput.value.trim();
                    if (query) {
                        performGlobalSearch(query);
                    } else {
                        renderBrowseFolder(currentPath);
                    }
                }

                function renderBrowseFolder(path) {
                    currentPath = path;
                    updateBreadcrumbs(path);
                    viewModeBadge.innerHTML = '<span class="badge badge-info">Browse Mode</span>';

                    const children = getDirectChildren(allFileData, path);
                    displayData(children);
                }

                function performGlobalSearch(query) {
                    viewModeBadge.innerHTML = '<span class="badge badge-warning">Global Search</span>';
                    updateBreadcrumbs(null); // Clear breadcrumbs or show "Search Results"

                    const typeFilter = $('.filter-type.active').data('type');
                    const isCaseSensitive = document.getElementById('caseSensitiveSwitch').checked;

                    const normalizedQuery = isCaseSensitive ? query : query.toLowerCase();
                    const keywords = normalizedQuery.split(' ').filter(k => k.length > 0);

                    const filtered = allFileData.filter(file => {
                        const typeMatch = typeFilter === 'all' || file.type === typeFilter;

                        const nameToSearch = isCaseSensitive ? file.name : file.name.toLowerCase();
                        const pathToSearch = isCaseSensitive ? file.path : file.path.toLowerCase();

                        const textMatch = keywords.every(kw => nameToSearch.includes(kw) || pathToSearch
                            .includes(kw));

                        return typeMatch && textMatch;
                    });

                    displayData(filtered);
                }

                function getDirectChildren(data, path) {
                    if (!path) {
                        return data.filter(item => !item.path.includes('/'));
                    }
                    const prefix = path + '/';
                    return data.filter(item => {
                        if (item.path === path) return false; // Don't show self
                        if (!item.path.startsWith(prefix)) return false;
                        const relativePart = item.path.substring(prefix.length);
                        return !relativePart.includes('/');
                    });
                }

                function displayData(data) {
                    if (data.length === 0) {
                        fileListBody.innerHTML =
                            '<tr><td colspan="3" class="text-center py-5">Empty folder or no results found.</td></tr>';
                        const foldersCount = 0;
                        const filesCount = 0;
                        resultsCountStr.textContent = `0 items found (0 folders, 0 files)`;
                        return;
                    }

                    let html = '';
                    data.sort((a, b) => (b.type === 'folder') - (a.type === 'folder')); // Folders first

                    data.forEach(file => {
                        const icon = file.type === 'folder' ?
                            '<i class="fas fa-folder fa-lg text-warning mr-3"></i>' :
                            '<i class="far fa-file-alt fa-lg text-info mr-3"></i>';

                        const badgeClass = file.type === 'folder' ? 'badge-soft-warning' : 'badge-soft-info';
                        const weightClass = file.type === 'folder' ? 'font-weight-bold font-italic' : '';

                        html += `
                            <tr class="file-item ${weightClass}" data-path="${file.path}" data-type="${file.type}" ${file.type === 'folder' ? 'onclick="goToPath(\'' + file.path + '\')"' : ''}>
                                <td class="px-4 align-middle">
                                    <div class="d-flex align-items-center">
                                        ${icon}
                                        <div>
                                            <div class="file-name">${file.name}</div>
                                            <small class="text-muted d-block text-break">${file.path}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <span class="badge ${badgeClass} px-2 py-1">${file.type.charAt(0).toUpperCase() + file.type.slice(1)}</span>
                                </td>
                                <td class="align-middle text-right text-muted pr-4">${file.last_modified || '-'}</td>
                            </tr>`;
                    });

                    fileListBody.innerHTML = html;

                    const foldersCount = data.filter(f => f.type === 'folder').length;
                    const filesCount = data.filter(f => f.type === 'file').length;
                    resultsCountStr.textContent =
                        `${data.length} items found (${foldersCount} folders, ${filesCount} files)`;
                }

                function updateBreadcrumbs(path) {
                    if (path === null) {
                        breadcrumbList.innerHTML =
                            '<li class="breadcrumb-item"><a href="#" onclick="goToPath(\'\')"><i class="fas fa-home"></i> Root</a></li><li class="breadcrumb-item active">Search Results</li>';
                        return;
                    }

                    let html = '<li class="breadcrumb-item ' + (path === '' ? 'active' : '') + '">';
                    if (path === '') {
                        html += '<i class="fas fa-home"></i> Root</li>';
                    } else {
                        html += '<a href="#" onclick="goToPath(\'\')"><i class="fas fa-home"></i> Root</a></li>';

                        const parts = path.split('/');
                        let currentAcc = '';
                        parts.forEach((part, index) => {
                            currentAcc += (index > 0 ? '/' : '') + part;
                            if (index === parts.length - 1) {
                                html +=
                                    `<li class="breadcrumb-item active text-truncate" style="max-width: 150px;">${part}</li>`;
                            } else {
                                html +=
                                    `<li class="breadcrumb-item"><a href="#" onclick="goToPath('${currentAcc}')">${part}</a></li>`;
                            }
                        });
                    }
                    breadcrumbList.innerHTML = html;
                }

                window.goToPath = function(path) {
                    searchInput.value = ''; // Clear search when navigating
                    renderBrowseFolder(path);
                };

                searchInput.addEventListener('input', renderUI);

                document.getElementById('caseSensitiveSwitch').addEventListener('change', renderUI);

                $('.filter-type').on('click', function() {
                    $('.filter-type').removeClass('active');
                    $(this).addClass('active');
                    renderUI();
                });

                // Update custom file label
                $('.custom-file-input').on('change', function() {
                    let fileName = $(this).val().split('\\').pop();
                    $(this).next('.custom-file-label').addClass("selected").html(fileName);
                });

                // AJAX Upload Logic
                $('#uploadForm').on('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const $form = $(this);
                    const $submitBtn = $('#submitBtn');
                    const $uploadStatusUI = $('#uploadStatusUI');
                    const $loadingUI = $('#loadingUI');

                    // Show Loading
                    $uploadStatusUI.fadeOut(200, function() {
                        $loadingUI.fadeIn(200);
                        $submitBtn.prop('disabled', true);
                    });

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                show_message(response.message, 'success');
                                setTimeout(() => {
                                    location.reload(); // Refresh to update list
                                }, 1000);
                            }
                        },
                        error: function(xhr) {
                            $loadingUI.fadeOut(200, function() {
                                $uploadStatusUI.fadeIn(200);
                                $submitBtn.prop('disabled', false);
                            });

                            const error = xhr.responseJSON ? xhr.responseJSON.message :
                                'Terjadi kesalahan sistem.';
                            show_message(error, 'error');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
