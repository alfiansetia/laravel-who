 <!-- Modal Login -->
 <div class="modal fade" id="authModal" tabindex="-1" role="dialog" aria-labelledby="authModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content border-0 shadow-lg">
             <div class="modal-header bg-primary text-white">
                 <h5 class="modal-title" id="authModalLabel">
                     <i class="fas fa-lock mr-1"></i>Verifikasi Akses
                 </h5>
                 <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>

             <form id="envLoginForm">
                 <div class="modal-body">
                     <div class="form-group">
                         <label for="envPassword">Masukkan Password Akses</label>
                         <input type="password" class="form-control" id="envPassword" name="password"
                             placeholder="••••••••" required>
                     </div>
                     <div class="text-danger mt-2 d-none" id="envErrorMsg"></div>
                 </div>

                 <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">
                         <i class="fas fa-times"></i> Close</button>
                     <button type="submit" class="btn btn-primary">
                         <i class="fa fa-lock"></i> Login
                     </button>
                 </div>
             </form>
         </div>
     </div>
 </div>

  @push('js')
      <script>
          $(document).ready(function() {

              // State awal navbar sudah di-render server-side (nav.blade.php pakai
              // EnvAuth::check()), jadi TIDAK ada AJAX auth.status tiap page load.
              // Satu-satunya refresh: reload halaman setelah login sukses, dan
              // popup modal otomatis kalau ada request yang 401 (session expired).

              $(document).on('ajaxError', function(event, jqXHR, settings, thrownError) {
                  if (jqXHR.status == 401) {
                      $('#authModal').modal('show')
                  }
              });

              $('#authModal').on('shown.bs.modal', function() {
                  $('#envPassword').focus()
              });

              $('#envLoginForm').on('submit', function(e) {
                  e.preventDefault();

                  let password = $('#envPassword').val().trim();
                  let $btn = $(this).find('button[type="submit"]');
                  let $error = $('#envErrorMsg');

                  $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Verifikasi...');
                  $error.addClass('d-none').text('');

                  $.ajax({
                      url: '{{ route('auth.verify') }}',
                      type: 'POST',
                      data: {
                          password: password,
                      },
                      success: function(res) {
                          if (res.data.auth) {
                              $('#authModal').modal('hide');
                              // Server render ulang navbar sesuai session baru
                              location.reload();
                          } else {
                              $error.removeClass('d-none').text('Password salah.');
                          }
                      },
                      error: function(xhr) {
                          $error.removeClass('d-none').text('Password tidak valid.');
                          $('#envPassword').focus()
                      },
                      complete: function() {
                          $btn.prop('disabled', false).html('<i class="fa fa-lock"></i> Login');
                          $('#envPassword').val('');
                      }
                  });
              });

          });
      </script>
  @endpush
