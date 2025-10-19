@extends('template')

@section('content')
    <div class="container-fluid">
        <form method="POST" action="" id="form">
            <div class="card card-primary mt-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">{{ $title }} </h3>
                </div>

                @csrf
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <input type="text" disabled class="form-control" id="odoo_session_name"
                                placeholder="ODOO SESSION USER">
                        </div>
                        <div class="form-group col-md-6">
                            <input type="text" disabled class="form-control" id="odoo_session_username"
                                placeholder="ODOO SESSION USERNAME">
                        </div>
                        <div class="form-group col-md-6">
                            <textarea class="form-control" id="odoo_env" placeholder="ODOO SESSION" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Kembali
                    </a>
                    <button type="button" id="btn_refresh" class="btn btn-warning">
                        <i class="fas fa-sync mr-1"></i>Refresh
                    </button>
                    <button type="button" id="btn_fix" class="btn btn-danger">
                        <i class="fas fa-hammer mr-1"></i>Fix Session
                    </button>
                    <button type="submit" id="btn_simpan" class="btn btn-primary">
                        <i class="fab fa-telegram-plane mr-1"></i>Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script>
        const URL_INDEX_API = "{{ route('api.settings.index') }}"
        $(document).ready(function() {
            getData()

            function getData() {
                $.ajax({
                    url: URL_INDEX_API,
                    type: 'GET',
                    beforeSend: function() {},
                    success: function(res) {
                        $('#odoo_env').val(res.data.session_id)
                        $('#odoo_session_username').val(res.data.username)
                        $('#odoo_session_name').val(`${res.data.name} (${res.data.uid})`)
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            }

            $('#btn_refresh').click(function() {
                getData()
            })

            $('#form').submit(function(e) {
                e.preventDefault()
                let odoo_env = $('#odoo_env').val();
                $.ajax({
                    url: URL_INDEX_API,
                    type: 'POST',
                    data: {
                        env_value: odoo_env,
                    },
                    beforeSend: function() {},
                    success: function(res) {
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })


            $('#btn_fix').click(function() {
                $.ajax({
                    url: URL_INDEX_API,
                    type: 'PUT',
                    beforeSend: function() {},
                    success: function(res) {
                        getData();
                        show_message(res.message, 'success')
                    },
                    error: function(xhr, status, error) {
                        show_message(xhr.responseJSON.message || 'Error!')
                    }
                });
            })

        });
    </script>
@endpush
