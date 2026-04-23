@extends('template')

@section('content')
    <div class="container-fluid px-4 py-3">
        <!-- Header with Gradient Title -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between p-4 rounded-lg shadow-sm border-0"
                    style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); color: white;">
                    <div>
                        <h2 class="font-weight-bold mb-1 ml-2"><i class="fas fa-search-location mr-2 text-warning"></i> File
                            Search Manager</h2>
                        <p class="mb-0 opacity-75 ml-2 font-weight-light">Pencarian file terpusat menggunakan index JSON.</p>
                        <div class="d-flex flex-wrap mt-3 ml-2" style="gap: 10px;">
                            <span class="badge btn-white-soft px-3 py-2 font-weight-light">
                                <i class="fas fa-clock mr-1 opacity-75"></i> Last Updated: <span
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
                                placeholder="Cari file atau folder dalam index..." autofocus>
                            <div class="input-group-append">
                                <span class="input-group-text bg-light border-0 px-3 text-muted" id="resultsCount">
                                    0 items found
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 px-2 d-flex align-items-center flex-wrap" style="gap: 20px;">
                            <div class="d-flex align-items-center">
                                <span class="small text-muted mr-2 font-weight-bold">Filter Type:</span>
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
                                    for="caseSensitiveSwitch">Case Sensitive Search</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="px-4 py-3 bg-light border-bottom d-flex align-items-center justify-content-between">
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
                                            <div class="mt-2 text-muted">Memuat data index...</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-clock mr-1"></i> Index source:
                            {{ $lastUpdated ? 'file-index.json' : 'Not Uploaded' }}
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
                                    upload di sini.
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

            .file-name {
                font-size: 0.95rem;
                color: #334155;
            }

            .rounded-lg {
                border-radius: 12px;
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
            let currentPath = '';

            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const fileListBody = document.getElementById('fileListBody');
                const resultsCountStr = document.getElementById('resultsCount');
                const breadcrumbList = document.getElementById('breadcrumbList');
                const viewModeBadge = document.getElementById('viewModeBadge');

                fetchData();

                function fetchData() {
                    $.get('{{ route('tools.file_search.data') }}', function(data) {
                        allFileData = data;
                        renderUI();
                        updateStats();
                    }).fail(function() {
                        fileListBody.innerHTML =
                            '<tr><td colspan="3" class="text-center py-5">Index not found. Please upload index JSON.</td></tr>';
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
                    updateBreadcrumbs(null);
                    const typeFilter = $('.filter-type.active').data('type');
                    const isCaseSensitive = document.getElementById('caseSensitiveSwitch').checked;
                    const normalizedQuery = isCaseSensitive ? query : query.toLowerCase();
                    const keywords = normalizedQuery.split(' ').filter(k => k.length > 0);

                    const filtered = allFileData.filter(file => {
                        const typeMatch = typeFilter === 'all' || file.type === typeFilter;
                        const nameToSearch = isCaseSensitive ? file.name : file.name.toLowerCase();
                        const pathToSearch = isCaseSensitive ? file.path : file.path.toLowerCase();
                        return typeMatch && keywords.every(kw => nameToSearch.includes(kw) || pathToSearch
                            .includes(kw));
                    });
                    displayData(filtered, query);
                }

                function getDirectChildren(data, path) {
                    if (!path) return data.filter(item => !item.path.includes('/'));
                    const prefix = path + '/';
                    return data.filter(item => {
                        if (item.path === path) return false;
                        if (!item.path.startsWith(prefix)) return false;
                        return !item.path.substring(prefix.length).includes('/');
                    });
                }

                function highlightText(text, query, isCaseSensitive) {
                    if (!query) return text;
                    const keywords = query.split(' ').filter(k => k.length > 0);
                    if (keywords.length === 0) return text;

                    const pattern = keywords.map(k => k.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
                    const regex = new RegExp(`(${pattern})`, isCaseSensitive ? 'g' : 'gi');
                    return text.replace(regex, '<b class="text-primary">$1</b>');
                }

                function displayData(data, query = '') {
                    if (data.length === 0) {
                        fileListBody.innerHTML =
                            '<tr><td colspan="3" class="text-center py-5">No results found.</td></tr>';
                        resultsCountStr.textContent = '0 items found';
                        return;
                    }

                    const isCaseSensitive = document.getElementById('caseSensitiveSwitch').checked;
                    data.sort((a, b) => (b.type === 'folder') - (a.type === 'folder'));
                    let html = '';
                    data.forEach(file => {
                        const icon = file.type === 'folder' ?
                            '<i class="fas fa-folder fa-lg text-warning mr-3"></i>' :
                            '<i class="far fa-file-alt fa-lg text-info mr-3"></i>';

                        const isFolder = file.type === 'folder';
                        const highlightedName = highlightText(file.name, query, isCaseSensitive);
                        const highlightedPath = highlightText(file.path, query, isCaseSensitive);

                        html += `
                            <tr class="file-item" data-type="${file.type}" onclick="${isFolder ? 'goToPath(\'' + file.path + '\')' : ''}">
                                <td class="px-4 align-middle">
                                    <div class="d-flex align-items-center">
                                        ${icon}
                                        <div>
                                            <div class="file-name font-weight-bold">${highlightedName}</div>
                                            <small class="text-dark d-block text-break opacity-75">${highlightedPath}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <span class="badge ${isFolder ? 'badge-soft-warning' : 'badge-soft-info'} px-2 py-1">${file.type.charAt(0).toUpperCase() + file.type.slice(1)}</span>
                                </td>
                                <td class="align-middle text-right text-muted pr-4">${file.last_modified || '-'}</td>
                            </tr>`;
                    });
                    fileListBody.innerHTML = html;
                    const fCount = data.filter(f => f.type === 'folder').length;
                    const fiCount = data.filter(f => f.type === 'file').length;
                    resultsCountStr.textContent = `${data.length} items (${fCount} folders, ${fiCount} files)`;
                }

                function updateBreadcrumbs(path) {
                    if (path === null) {
                        breadcrumbList.innerHTML =
                            '<li class="breadcrumb-item"><a href="#" onclick="goToPath(\'\')">Root</a></li><li class="breadcrumb-item active">Search</li>';
                        return;
                    }
                    let html =
                        `<li class="breadcrumb-item ${path === '' ? 'active' : ''}"><a href="#" onclick="goToPath('')">Root</a></li>`;
                    if (path) {
                        const parts = path.split('/');
                        let acc = '';
                        parts.forEach((p, i) => {
                            acc += (i > 0 ? '/' : '') + p;
                            html +=
                                `<li class="breadcrumb-item ${i === parts.length - 1 ? 'active' : ''}"><a href="#" onclick="goToPath('${acc}')">${p}</a></li>`;
                        });
                    }
                    breadcrumbList.innerHTML = html;
                }

                function updateStats() {
                    const fo = allFileData.filter(f => f.type === 'folder').length;
                    const fi = allFileData.filter(f => f.type === 'file').length;
                    document.getElementById('headerTotalStats').textContent = `${fo} folders, ${fi} files`;
                    document.getElementById('footerTotalCount').textContent = allFileData.length;
                }

                window.goToPath = function(p) {
                    searchInput.value = '';
                    renderBrowseFolder(p);
                };
                searchInput.addEventListener('input', renderUI);
                document.getElementById('caseSensitiveSwitch').addEventListener('change', renderUI);
                $('.filter-type').on('click', function() {
                    $('.filter-type').removeClass('active');
                    $(this).addClass('active');
                    renderUI();
                });

                // AJAX Upload Logic
                $('.custom-file-input').on('change', function() {
                    $(this).next('.custom-file-label').html($(this).val().split('\\').pop());
                });
                $('#uploadForm').on('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    $('#uploadStatusUI').fadeOut(200, () => {
                        $('#loadingUI').fadeIn(200);
                        $('#submitBtn').prop('disabled', true);
                    });
                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: (res) => {
                            if (res.success) {
                                show_message(res.message, 'success');
                                setTimeout(() => location.reload(), 800);
                            }
                        },
                        error: (xhr) => {
                            $('#loadingUI').fadeOut(200, () => {
                                $('#uploadStatusUI').fadeIn(200);
                                $('#submitBtn').prop('disabled', false);
                            });
                            show_message(xhr.responseJSON?.message || 'Error uploading.', 'error');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
