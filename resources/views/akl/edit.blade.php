@extends('template', ['title' => 'Edit Lampiran AKL'])

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-edit mr-2 text-warning"></i>Edit Lampiran AKL</h5>
                    </div>
                    <form action="{{ route('akls.update', $akl->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label>Reg No <span class="text-danger">*</span></label>
                                <input type="text" name="reg_no" class="form-control @error('reg_no') is-invalid @enderror"
                                    value="{{ old('reg_no', $akl->reg_no) }}" required>
                                @error('reg_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Nama Registrasi</label>
                                <input type="text" name="reg_name" class="form-control @error('reg_name') is-invalid @enderror"
                                    value="{{ old('reg_name', $akl->reg_name) }}">
                                @error('reg_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Vendor</label>
                                <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror"
                                    value="{{ old('vendor', $akl->vendor) }}">
                                @error('vendor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Berlaku Dari</label>
                                    <input type="date" name="date_from" class="form-control @error('date_from') is-invalid @enderror"
                                        value="{{ old('date_from', $akl->date_from?->format('Y-m-d')) }}">
                                    @error('date_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tanggal Expired</label>
                                    <input type="date" name="date_expired" class="form-control @error('date_expired') is-invalid @enderror"
                                        value="{{ old('date_expired', $akl->date_expired?->format('Y-m-d')) }}">
                                    @error('date_expired')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Ganti File (opsional)</label>
                                <div class="custom-file">
                                    <input type="file" name="file" id="aklFile"
                                        class="custom-file-input @error('file') is-invalid @enderror"
                                        accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    <label class="custom-file-label" for="aklFile">Pilih file baru...</label>
                                </div>
                                <small class="form-text text-muted">Kosongkan jika tidak ganti. File lama di S3
                                    ikut terhapus saat diganti.</small>
                                @error('file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @include('akl.partials._preview', [
                                'currentUrl' => $akl->file ? route('akls.show', $akl->id) : null,
                                'isPdf' => $akl->is_pdf,
                            ])
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('akls.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card" style="position: sticky; top: 80px;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-file-alt mr-2 text-info"></i>Preview Dokumen</h5>
                        @if ($akl->file)
                            <a href="{{ route('akls.show', $akl->id) }}" target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-external-link-alt mr-1"></i> Buka tab baru
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>File Saat Ini</label><br>
                            @if ($akl->file)
                                <small class="text-muted">{{ $akl->file }}</small>
                            @else
                                <span class="text-muted">Belum ada lampiran.</span>
                            @endif
                        </div>
                        @include('akl.partials._preview', [
                            'currentUrl' => $akl->file ? route('akls.show', $akl->id) : null,
                            'isPdf' => $akl->is_pdf,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $('#aklFile').on('change', function() {
            var name = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').text(name || 'Pilih file baru...');
            aklPreviewSelected(this.files && this.files[0]);
        });
    </script>
@endpush
