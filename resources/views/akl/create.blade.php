@extends('template', ['title' => 'Upload Lampiran AKL'])

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-upload mr-2 text-primary"></i>Upload Lampiran AKL</h5>
                    </div>
                    <form action="{{ route('akls.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="alert alert-info py-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Reg No boleh sama</strong> dengan data lain (mis. perpanjangan dengan
                                tanggal expired & lampiran yang berbeda) — setiap upload tersimpan sebagai
                                baris dokumen tersendiri.
                            </div>

                            <div class="form-group">
                                <label>Reg No <span class="text-danger">*</span></label>
                                <input type="text" name="reg_no" class="form-control @error('reg_no') is-invalid @enderror"
                                    value="{{ old('reg_no') }}" placeholder="cth: AKL1234567890" required>
                                @error('reg_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Nama Registrasi</label>
                                <input type="text" name="reg_name" class="form-control @error('reg_name') is-invalid @enderror"
                                    value="{{ old('reg_name') }}" placeholder="Nama produk / registrasi">
                                @error('reg_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Vendor</label>
                                <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror"
                                    value="{{ old('vendor') }}" placeholder="Nama vendor">
                                @error('vendor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Berlaku Dari</label>
                                    <input type="date" name="date_from" class="form-control @error('date_from') is-invalid @enderror"
                                        value="{{ old('date_from') }}">
                                    @error('date_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tanggal Expired</label>
                                    <input type="date" name="date_expired" class="form-control @error('date_expired') is-invalid @enderror"
                                        value="{{ old('date_expired') }}">
                                    @error('date_expired')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label>File Lampiran (PDF / Gambar) <span class="text-muted">(opsional)</span></label>
                                <div class="custom-file">
                                    <input type="file" name="file" id="aklFile"
                                        class="custom-file-input @error('file') is-invalid @enderror"
                                        accept=".jpg,.jpeg,.png,.webp,.pdf">
                                    <label class="custom-file-label" for="aklFile">Pilih file...</label>
                                </div>
                                <small class="form-text text-muted">Boleh kosong. Format: JPG, PNG, WEBP, PDF. Maksimal 20MB.
                                    File tersimpan di S3.</small>
                                @error('file')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            @include('akl.partials._preview')
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('akls.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-upload mr-1"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $('#aklFile').on('change', function() {
            var name = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').text(name || 'Pilih file...');
            aklPreviewSelected(this.files && this.files[0]);
        });
    </script>
@endpush
