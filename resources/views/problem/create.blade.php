@extends('template')
@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.css') }}">
@endpush

@section('content')
    <div class="container-fluid">

        <form method="POST" action="{{ route('problem.store') }}" id="form">
            @csrf
            <div class="card card-primary mt-3">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }} </h3>
                </div>

                <div class="card-body">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="date">Date</label>
                            <input type="date" name="date" id="date" class="form-control" placeholder="Date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="number">Number</label>
                            <input type="text" name="number" id="number" class="form-control" placeholder="Number"
                                value="{{ strtoupper(date('Y-M-')) }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control">
                                <option value="dus">Dus</option>
                                <option value="unit">Unit</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="stock">Stock</label>
                            <select name="stock" id="stock" class="form-control">
                                <option value="stock">Stock</option>
                                <option value="import">Import</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="ri_po">RI/PO</label>
                            <input type="text" name="ri_po" id="ri_po" class="form-control" placeholder="RI/PO">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email_on">Email On</label>
                            <input type="date" name="email_on" id="email_on" class="form-control"
                                placeholder="Email On">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="done">Done</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pic">PIC</label>
                            <input type="text" name="pic" id="pic" class="form-control" placeholder="PIC"
                                value="Karim" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('alamats.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" id="btn_simpan" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {


        });
    </script>
@endpush
