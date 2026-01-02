@php
    $title = $title ?? '';
@endphp
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.1/css/all.min.css"
        integrity="sha512-gMjQeDaELJ0ryCI+FtItusU9MkAifCZcGq789FrzkiM49D8lbDhoaUaIX4ASU187wofMNlgBJ4ckbrXM9sE6Pg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <link
        href="https://cdn.datatables.net/v/bs4-4.6.0/jq-3.7.0/moment-2.29.4/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/r-3.0.7/datatables.min.css"
        rel="stylesheet" integrity="sha384-xa0J5dynFOZAK0O53KhisKyzwnrzrVuK9m1YzzpkCXuLxY2Xp+hSh2ICCzhZbT0O"
        crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/css/iziToast.min.css"
        integrity="sha512-O03ntXoVqaGUTAeAmvQ2YSzkCvclZEcPQu1eqloPaHfJ5RuNGiS4l+3duaidD801P50J28EHyonCV06CUlTSag=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @stack('css')
    <title>{{ $title }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/asa.png') }}" />
    @include('pwa.head')
</head>


<body>
    @include('components.nav')

    <div id="notif" class="mt-2 pl-3 pr-3">

    </div>
    <div class="content-wrapper">
        @include('components.breadcumb')
        <!-- Main content -->
        <section class="content">
            @yield('content')
        </section>

    </div>
    <br>
    <br>
    <br>
    <br>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.8.1/js/all.min.js"
        integrity="sha512-jAu66pqHWWQ564NS+m2Zxe13Yek98R7JWNjQLzW+PQ4i2jsMxBT1nGrQ0gFUIVJ4kPkEFe5gelBWNEDTBqmn/w=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"
        integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"
        integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n" crossorigin="anonymous">
    </script>
    <script
        src="https://cdn.datatables.net/v/bs4-4.6.0/jq-3.7.0/moment-2.29.4/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/r-3.0.7/sl-3.1.3/datatables.min.js"
        integrity="sha384-LPjI9U3tjuqNOSXdATtz6hGmWt/t5IQZqRbSvJbghCAadWjkd5PBBtUsBfT+nPXK" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"
        integrity="sha384-+YQ4JLhjyBLPDQt//I+STsc9iw4uQqACwlvpslubQzn4u2UU2UFM80nGisd026JF" crossorigin="anonymous">
    </script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/block-ui@2.70.1/jquery.blockUI.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/izitoast/1.4.0/js/iziToast.min.js"
        integrity="sha512-Zq9o+E00xhhR/7vJ49mxFNJ0KQw1E1TMWkPTxrWcnpfEFDEXgUiwJHIKit93EW/XxE31HSI5GEOW06G6BF1AtA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    @include('components.auth')


    @include('pwa.script')

    <script>
        function show_message(message = 'Kesalahan tidak diketahui!', type = 'error') {
            if (type == 'success') {
                iziToast.success({
                    title: 'Success',
                    message: message,
                    position: 'topRight',
                });
            } else if (type == 'warning') {
                iziToast.warning({
                    title: 'Caution',
                    message: message,
                    position: 'topRight',
                });
            } else if (type == 'info') {
                iziToast.info({
                    title: 'Hello',
                    message: message,
                    position: 'topRight',
                });
            } else {
                iziToast.error({
                    title: 'Error',
                    message: message,
                    position: 'topRight',
                });
            }

        }

        function confirmation(message = '', callback) {
            iziToast.question({
                timeout: 0,
                close: false,
                overlay: true,
                displayMode: 'once',
                id: 'question',
                zindex: 999,
                title: 'Konfirmasi',
                message: message,
                position: 'center',
                buttons: [
                    ['<button><i class="fas fa-thumbs-up mr-1"></i><b>Yes</b></button>', function(instance,
                        toast) {
                        instance.hide({
                            transitionOut: 'fadeOut'
                        }, toast, 'button');
                        if (callback) callback(true);
                    }, true],
                    ['<button><i class="fas fa-thumbs-down mr-1"></i>Cancel</button>', function(instance,
                        toast) {
                        instance.hide({
                            transitionOut: 'fadeOut'
                        }, toast, 'button');
                        if (callback) callback(false);
                    }]
                ]
            });
        }

        function danger(message) {
            let alert = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`
            $('#notif').html(alert)
        }

        function success(message) {
            let alert = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                ${message} 
                <button type="button" class="btn btn-sm btn-primary" onclick="test_notif()">
                    Cobain
                </button>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`
            $('#notif').html(alert)
        }

        function bloc() {
            $.blockUI({
                message: '<img src="{{ asset('images/loading.gif') }}" width="20px" height="20px" /> Just a moment...',
                baseZ: 2000,
            });
        }

        $(document).ready(function() {
            let blockTimeout;

            $(document).ajaxSend(function() {
                // Clear timeout lama jika masih ada (mencegah bentrok antar request)
                if (blockTimeout) clearTimeout(blockTimeout);

                $.blockUI({
                    message: '<img src="{{ asset('images/loading.gif') }}" width="20px" height="20px" /> Just a moment...',
                    baseZ: 2000,
                });

                // Set timeout untuk auto unblock setelah 10 detik
                blockTimeout = setTimeout(function() {
                    $.unblockUI();
                }, 10000);
            });

            $(document).ajaxComplete(function() {
                if (blockTimeout) clearTimeout(blockTimeout);
                $.unblockUI();
            });

            $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
                // console.error('AJAX Error:', thrownError);
                // Notifikasi error opsional
                // show_message('Terjadi kesalahan pada server', 'error');
            });

            bsCustomFileInput.init();
        })

        function multiCheck(tb_var) {
            tb_var.on("change", ".chk-parent", function() {
                    var e = $(this).closest("table").find("td:first-child .child-chk"),
                        a = $(this).is(":checked");
                    $(e).each(function() {
                        a ? ($(this).prop("checked", !0), $(this).closest("tr").addClass("active")) : ($(this)
                            .prop("checked", !1), $(this).closest("tr").removeClass("active"))
                    })
                }),
                tb_var.on("change", "tbody tr .new-control", function() {
                    $(this).parents("tr").toggleClass("active")
                })
        }
    </script>

    @if (session()->has('message'))
        <script>
            $(document).ready(function() {
                show_message("{{ session('message') }}")
            })
        </script>
    @endif

    @if (session()->has('error'))
        <script>
            $(document).ready(function() {
                show_message("{{ session('error') }}", 'error')
            })
        </script>
    @endif

    @if (session()->has('success'))
        <script>
            $(document).ready(function() {
                show_message("{{ session('success') }}", 'success')
            })
        </script>
    @endif

    @include('components.notif')

    @stack('js')

</body>


</html>
