@extends('template', ['title' => 'Kalkulator Nilai'])
@push('css')
    <style>
        .bg-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .bg-gradient-total {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .perhitungan-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            border: 2px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .perhitungan-item:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.75rem;
            margin-bottom: 4px;
        }

        .form-control-sm {
            border-radius: 6px;
            border: 1px solid #dee2e6;
            padding: 6px 10px;
            font-size: 0.875rem;
        }

        .form-control-sm:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.15rem rgba(102, 126, 234, 0.15);
        }

        .result-inline {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 6px;
            padding: 8px 12px;
            color: white;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .result-inline strong {
            font-size: 1rem;
        }

        .form-check-input {
            cursor: pointer;
        }

        .form-check-label {
            cursor: pointer;
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-lg border-0">
                    <div class="card-body p-3">
                        <!-- Tombol Tambah -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary btn-sm" onclick="tambahPerhitungan()">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>

                        <!-- Container untuk semua perhitungan -->
                        <div id="perhitunganContainer" class="mb-3">
                            <!-- Perhitungan akan ditambahkan di sini -->
                        </div>

                        <!-- Total Keseluruhan -->
                        <div class="card border-0 bg-gradient-total">
                            <div class="card-body py-2 px-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="text-white mb-0"><i class="fas fa-money-bill-wave"></i>
                                        Total Keseluruhan
                                    </h5>
                                    <h4 class="text-white mb-0 fw-bold" id="grandTotal">Rp 0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js"
        integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        let perhitunganCounter = 0;


        function formatRupiah(angka) {
            return 'Rp ' + angka.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        function toggleSameAsTotal(id) {
            const checkbox = document.getElementById(`samakan-${id}`);
            const jumlahKirimInput = document.getElementById(`jumlahKirim-${id}`);
            const jumlahTotal = document.getElementById(`jumlahTotal-${id}`);

            if (checkbox.checked) {
                jumlahKirimInput.value = jumlahTotal.value;
                jumlahKirimInput.disabled = true;
            } else {
                jumlahKirimInput.disabled = false;
            }
            hitungPerhitungan(id);
        }

        function updateJumlahTotal(id) {
            const checkbox = document.getElementById(`samakan-${id}`);
            if (checkbox.checked) {
                const jumlahTotal = document.getElementById(`jumlahTotal-${id}`);
                const jumlahKirimInput = document.getElementById(`jumlahKirim-${id}`);
                jumlahKirimInput.value = jumlahTotal.value;
            }
            hitungPerhitungan(id);
        }

        function tambahPerhitungan() {
            perhitunganCounter++;
            const container = document.getElementById('perhitunganContainer');

            const perhitunganHTML = `
                <div class="perhitungan-item" id="perhitungan-${perhitunganCounter}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-1">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <span class="badge bg-primary nomor-badge"></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total Nilai (sebelum pajak)</label>
                            <input type="text" class="form-control form-control-sm mask_angka" id="totalNilai-${perhitunganCounter}" 
                                placeholder="Nilai Sebelum Pajak" value="0" min="0" step="1000"
                                oninput="hitungPerhitungan(${perhitunganCounter})">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah Total</label>
                            <input type="number" class="form-control form-control-sm" id="jumlahTotal-${perhitunganCounter}" 
                                placeholder="10" value="1" min="1"
                                oninput="updateJumlahTotal(${perhitunganCounter})">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah Kirim</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="jumlahKirim-${perhitunganCounter}" 
                                    placeholder="5" value="1" min="0" disabled
                                    oninput="hitungPerhitungan(${perhitunganCounter})">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input  type="checkbox" id="samakan-${perhitunganCounter}" 
                                            checked onchange="toggleSameAsTotal(${perhitunganCounter})">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Pajak (%)</label>
                            <input type="number" class="form-control form-control-sm" id="persenPajak-${perhitunganCounter}" 
                                placeholder="11" value="11" min="0" max="100" step="0.1"
                                oninput="hitungPerhitungan(${perhitunganCounter})">
                        </div>
                        <div class="col-md-3">
                            <div class="result-inline">
                                <span>Total:</span>
                                <strong id="totalAkhir-${perhitunganCounter}">Rp 0</strong>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-warning" onclick="duplikatPerhitungan(${perhitunganCounter})" title="Duplikat">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="hapusPerhitungan(${perhitunganCounter})" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', perhitunganHTML);

            // Initialize mask untuk input yang baru ditambahkan
            initializeMasks();

            // Set initial value untuk jumlah kirim sama dengan jumlah total
            const jumlahTotalInput = document.getElementById(`jumlahTotal-${perhitunganCounter}`);
            const jumlahKirimInput = document.getElementById(`jumlahKirim-${perhitunganCounter}`);
            jumlahKirimInput.value = jumlahTotalInput.value;
            hitungPerhitungan(perhitunganCounter);

            // Update nomor urut
            updateNomor();
        }

        function updateNomor() {
            const container = document.getElementById('perhitunganContainer');
            const badges = container.querySelectorAll('.nomor-badge');
            badges.forEach((badge, index) => {
                badge.textContent = `#${index + 1}`;
            });
        }

        function initializeMasks() {
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

        function hitungPerhitungan(id) {
            const totalNilai = parseFloat(document.getElementById(`totalNilai-${id}`).value) || 0;
            const jumlahTotal = parseFloat(document.getElementById(`jumlahTotal-${id}`).value) || 1;
            const jumlahKirim = parseFloat(document.getElementById(`jumlahKirim-${id}`).value) || 0;
            const persenPajak = parseFloat(document.getElementById(`persenPajak-${id}`).value) || 0;

            // Hitung sesuai rumus yang benar
            const hargaPerBarang = totalNilai / jumlahTotal;
            const subtotal = hargaPerBarang * jumlahKirim;
            const nilaiPajak = subtotal * (persenPajak / 100);
            const totalAkhir = subtotal + nilaiPajak;

            // Update tampilan
            document.getElementById(`totalAkhir-${id}`).textContent = formatRupiah(totalAkhir);

            // Update grand total
            hitungGrandTotal();
        }

        function hapusPerhitungan(id) {
            const element = document.getElementById(`perhitungan-${id}`);
            if (element) {
                element.remove();
                updateNomor();
                hitungGrandTotal();
            }
        }

        function duplikatPerhitungan(id) {
            // Ambil nilai dari perhitungan yang akan diduplikat
            const totalNilai = document.getElementById(`totalNilai-${id}`).value;
            const jumlahTotal = document.getElementById(`jumlahTotal-${id}`).value;
            const jumlahKirim = document.getElementById(`jumlahKirim-${id}`).value;
            const persenPajak = document.getElementById(`persenPajak-${id}`).value;
            const checkboxChecked = document.getElementById(`samakan-${id}`).checked;

            // Buat perhitungan baru
            perhitunganCounter++;
            const container = document.getElementById('perhitunganContainer');

            const perhitunganHTML = `
                <div class="perhitungan-item" id="perhitungan-${perhitunganCounter}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-1">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <span class="badge bg-primary nomor-badge"></span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total Nilai (sebelum pajak)</label>
                            <input type="text" class="form-control form-control-sm mask_angka" id="totalNilai-${perhitunganCounter}" 
                                placeholder="Nilai Sebelum Pajak" value="${totalNilai}" min="0" step="1000"
                                oninput="hitungPerhitungan(${perhitunganCounter})">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah Total</label>
                            <input type="number" class="form-control form-control-sm" id="jumlahTotal-${perhitunganCounter}" 
                                placeholder="10" value="${jumlahTotal}" min="1"
                                oninput="updateJumlahTotal(${perhitunganCounter})">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jumlah Kirim</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="jumlahKirim-${perhitunganCounter}" 
                                    placeholder="5" value="${jumlahKirim}" min="0" ${checkboxChecked ? 'disabled' : ''}
                                    oninput="hitungPerhitungan(${perhitunganCounter})">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="checkbox" id="samakan-${perhitunganCounter}" 
                                            ${checkboxChecked ? 'checked' : ''} onchange="toggleSameAsTotal(${perhitunganCounter})">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Pajak (%)</label>
                            <input type="number" class="form-control form-control-sm" id="persenPajak-${perhitunganCounter}" 
                                placeholder="11" value="${persenPajak}" min="0" max="100" step="0.1"
                                oninput="hitungPerhitungan(${perhitunganCounter})">
                        </div>
                        <div class="col-md-3">
                            <div class="result-inline">
                                <span>Total:</span>
                                <strong id="totalAkhir-${perhitunganCounter}">Rp 0</strong>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-warning btn-duplikat" onclick="duplikatPerhitungan(${perhitunganCounter})" title="Duplikat">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-hapus" onclick="hapusPerhitungan(${perhitunganCounter})" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', perhitunganHTML);

            // Initialize mask untuk input yang baru ditambahkan
            initializeMasks();

            // Hitung perhitungan untuk item baru
            hitungPerhitungan(perhitunganCounter);

            // Update nomor urut
            updateNomor();
        }

        function hitungGrandTotal() {
            let grandTotal = 0;
            const container = document.getElementById('perhitunganContainer');
            const perhitunganItems = container.querySelectorAll('.perhitungan-item');

            perhitunganItems.forEach((item) => {
                const id = item.id.split('-')[1];
                const totalAkhirElement = document.getElementById(`totalAkhir-${id}`);
                if (totalAkhirElement) {
                    const totalText = totalAkhirElement.textContent.replace(/[^0-9]/g, '');
                    grandTotal += parseFloat(totalText) || 0;
                }
            });

            document.getElementById('grandTotal').textContent = formatRupiah(grandTotal);
        }

        // Tambah perhitungan pertama saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize masks pertama kali
            initializeMasks();

            // Tambah perhitungan pertama
            tambahPerhitungan();
        });
    </script>
@endpush
