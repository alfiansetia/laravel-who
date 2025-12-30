@extends('template')
@push('css')
    <style>
        #result {
            height: 220px;
            font-size: 18px;
        }

        #status {
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header">
                <h3 class="text-center"><i class="fa fa-microphone"></i> Speech to Text (Web Speech API)</h3>
            </div>
            <div class="card-body">
                <div id="browserWarning" class="alert alert-warning d-none">
                    <i class="fa fa-exclamation-triangle"></i> <strong>Peringatan!</strong> Browser Anda tidak mendukung Web
                    Speech API.
                    Beberapa fitur mungkin tidak berfungsi. Kami menyarankan menggunakan <strong>Google Chrome</strong> atau
                    <strong>Microsoft Edge</strong>.
                </div>

                <div class="form-group text-capitalize">
                    <label for="result">Hasil Transkripsi:</label>
                    <textarea id="result" class="form-control" readonly placeholder="Mulai berbicara..."></textarea>
                </div>

                <div class="text-center mt-4">
                    <button id="startBtn" class="btn btn-primary btn-lg px-4 mb-1">
                        Mulai Rekam <i class="fa fa-microphone"></i>
                    </button>

                    <button id="stopBtn" class="btn btn-danger btn-lg px-4 mb-1" disabled>
                        Stop <i class="fa fa-stop"></i>
                    </button>

                    <button id="copyBtn" class="btn btn-success btn-lg px-4 mb-1">
                        Copy Text <i class="fa fa-copy"></i>
                    </button>
                </div>

                <p id="status" class="text-center text-success mt-3"></p>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        let recognition;
        let isRecording = false;
        let silenceTimer;
        const silenceTimeout = 4000; // 4 detik tanpa suara = berhenti otomatis

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        // Check Web Speech API support
        if (!SpeechRecognition) {
            document.getElementById("browserWarning").classList.remove("d-none");
            document.getElementById("startBtn").disabled = true;
            document.getElementById("copyBtn").disabled = true;
            document.getElementById("status").innerText = "Browser tidak mendukung fitur ini.";
            document.getElementById("status").className = "text-center text-danger mt-3";
        } else {
            recognition = new SpeechRecognition();
            recognition.lang = "id-ID";
            recognition.interimResults = true;
            recognition.continuous = true;

            // START LISTENING
            recognition.onstart = function() {
                document.getElementById("status").innerText = "🎧 Mendengarkan...";
                resetSilenceDetection();
            };

            // END LISTENING
            recognition.onend = function() {
                document.getElementById("status").innerText = "Berhenti mendengarkan.";

                // Auto-continue jika user tidak menekan stop
                if (isRecording) {
                    recognition.start();
                }
            };

            // PROCESS RESULT
            recognition.onresult = function(event) {
                let text = "";

                for (let i = 0; i < event.results.length; i++) {
                    text += event.results[i][0].transcript + " ";
                }

                text = text.trim();
                // Make the first letter uppercase
                if (text.length > 0) {
                    text = text.charAt(0).toUpperCase() + text.slice(1);
                }

                document.getElementById("result").value = text;

                resetSilenceDetection();
            };
        }

        // RESET TIMER KETIKA ADA SUARA
        function resetSilenceDetection() {
            clearTimeout(silenceTimer);
            silenceTimer = setTimeout(() => {
                stopRecording();
                document.getElementById("status").innerText = "⏸ Tidak ada suara, otomatis berhenti.";
            }, silenceTimeout);
        }

        // REQUEST MIC PERMISSION
        async function checkMicrophone() {
            try {
                await navigator.mediaDevices.getUserMedia({
                    audio: true
                });
                return true;
            } catch (err) {
                alert("Izin mikrofon ditolak. Harap izinkan mic untuk melanjutkan.");
                return false;
            }
        }

        // START RECORDING
        document.getElementById("startBtn").onclick = async function() {
            let micAllowed = await checkMicrophone();
            if (!micAllowed) return;

            recognition.start();
            isRecording = true;

            document.getElementById("startBtn").disabled = true;
            document.getElementById("stopBtn").disabled = false;
        };

        // STOP RECORDING
        document.getElementById("stopBtn").onclick = function() {
            stopRecording();
            document.getElementById("status").innerText = "Berhenti oleh pengguna.";
        };

        function stopRecording() {
            if (isRecording) {
                recognition.stop();
                isRecording = false;

                document.getElementById("startBtn").disabled = false;
                document.getElementById("stopBtn").disabled = true;
            }
        }

        // COPY TEXT
        document.getElementById("copyBtn").onclick = function() {
            const resultTextarea = document.getElementById("result");
            const text = resultTextarea.value;

            if (!text || text.trim() === "") {
                document.getElementById("status").innerText = "⚠️ Tidak ada teks untuk disalin.";
                document.getElementById("status").className = "text-center text-warning mt-3";
                return;
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    showCopySuccess();
                }).catch(err => {
                    fallbackCopyText(text);
                });
            } else {
                fallbackCopyText(text);
            }
        };

        function fallbackCopyText(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                showCopySuccess();
            } catch (err) {
                alert("Gagal menyalin teks. Silakan salin secara manual.");
            }
            document.body.removeChild(textArea);
        }

        function showCopySuccess() {
            document.getElementById("status").innerText = "📋 Teks disalin ke clipboard!";
            document.getElementById("status").className = "text-center text-success mt-3";
            setTimeout(() => {
                document.getElementById("status").innerText = "";
            }, 3000);
        }
    </script>
@endpush
