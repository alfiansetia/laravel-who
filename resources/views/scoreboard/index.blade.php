<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Scoreboard</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Bebas+Neue&family=Orbitron:wght@400;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0c0d12;
            --home-color: #00f2ff;
            --away-color: #ff003c;
            --accent-color: #ffd700;
            --card-bg: rgba(26, 28, 35, 0.8);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            user-select: none;
        }

        body {
            background-color: var(--bg-color);
            background-image:
                radial-gradient(circle at 20% 20%, rgba(0, 242, 255, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(255, 0, 60, 0.05) 0%, transparent 40%);
            color: #fff;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Container */
        .container {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* Header / Stats Bar */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--card-bg);
            padding: 15px 30px;
            border-radius: var(--border-radius);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .period-box {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .period-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 5px;
        }

        .period-value {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 32px;
            color: var(--accent-color);
        }

        /* Timer Section */
        .timer-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--card-bg);
            padding: 20px 60px;
            border-radius: var(--border-radius);
            border: 2px solid rgba(255, 255, 255, 0.05);
            min-width: 300px;
            position: relative;
        }

        .timer-display {
            font-family: 'Orbitron', sans-serif;
            font-size: 80px;
            font-weight: 700;
            color: #fff;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }

        .timer-display.running {
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.4);
        }

        .timer-controls {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        /* Scoreboard Grid */
        .scoreboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            flex: 1;
        }

        .team-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden;
            transition: var(--transition);
        }

        .team-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }

        .team-card.home::before {
            background: var(--home-color);
            box-shadow: 0 4px 15px var(--home-color);
        }

        .team-card.away::before {
            background: var(--away-color);
            box-shadow: 0 4px 15px var(--away-color);
        }

        .team-name {
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
            border: none;
            background: transparent;
            width: 100%;
            outline: none;
        }

        .score-display {
            font-family: 'Orbitron', sans-serif;
            font-size: clamp(80px, 15vw, 180px);
            font-weight: 900;
            line-height: 1;
            margin: 10px 0;
            transition: var(--transition);
        }

        .home .score-display {
            color: var(--home-color);
            text-shadow: 0 0 40px rgba(0, 242, 255, 0.3);
        }

        .away .score-display {
            color: var(--away-color);
            text-shadow: 0 0 40px rgba(255, 0, 60, 0.3);
        }

        /* Buttons Style */
        .btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 10px 15px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .btn:active {
            transform: scale(0.95);
        }

        .btn-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            padding: 0;
        }

        .btn-large {
            padding: 12px 30px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .btn-timer {
            background: #fff;
            color: #000;
            border: none;
        }

        .btn-timer:hover {
            background: #e0e0e0;
        }

        .btn-primary-glow {
            background: var(--home-color);
            color: #000;
            border: none;
        }

        .btn-secondary-glow {
            background: var(--away-color);
            color: #fff;
            border: none;
        }

        /* Controls Panel */
        .score-controls {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }

        /* Footer Controls */
        .footer-controls {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            padding-bottom: 20px;
        }

        /* Animations */
        @keyframes scorePop {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .score-animate {
            animation: scorePop 0.3s ease-out;
        }

        /* Settings Overlay */
        .settings-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 100;
            opacity: 0.3;
        }

        .settings-btn:hover {
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .score-display {
                font-size: clamp(60px, 12vw, 120px);
            }

            .timer-display {
                font-size: 60px;
            }
        }

        /* Mobile Portrait */
        @media (max-width: 768px) and (orientation: portrait) {
            .scoreboard-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .container {
                padding: 10px;
            }

            .header {
                padding: 10px;
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .timer-section {
                order: -1;
                width: 100%;
                min-width: auto;
                padding: 10px;
            }

            .timer-display {
                font-size: 50px;
            }

            .period-box {
                flex: 1;
            }

            .score-display {
                font-size: clamp(80px, 25vw, 150px);
            }
        }

        /* Mobile Landscape Optimization */
        @media (max-height: 500px) and (orientation: landscape) {
            .container {
                padding: 5px 15px;
                gap: 8px;
            }

            .header {
                padding: 5px 15px;
                gap: 15px;
            }

            .timer-section {
                padding: 5px 20px;
                width: auto;
                min-width: auto;
                order: 0;
            }

            .timer-display {
                font-size: 40px;
            }

            .period-value {
                font-size: 24px;
            }

            .scoreboard-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .team-card {
                padding: 10px;
            }

            .score-display {
                font-size: clamp(60px, 20vh, 100px);
                margin: 0;
            }

            .team-name {
                font-size: 18px;
                margin-bottom: 5px;
            }

            .score-controls {
                margin-top: 5px;
                gap: 5px;
            }

            .btn-circle {
                width: 40px;
                height: 40px;
                font-size: 14px;
            }

            .footer-controls {
                padding-bottom: 5px;
                gap: 8px;
            }

            .btn {
                padding: 5px 10px;
                font-size: 12px;
            }

            .settings-btn {
                bottom: 10px;
                right: 10px;
            }
        }

        /* Smallest Phones */
        @media (max-width: 480px) and (orientation: portrait) {
            .score-display {
                font-size: 100px;
            }

            .team-name {
                font-size: 18px;
            }

            .btn-circle {
                width: 45px;
                height: 45px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Stats Bar -->
        <div class="header">
            <div class="period-box">
                <span class="period-label">Period</span>
                <span class="period-value" id="period">1</span>
                <div class="d-flex gap-2" style="display: flex; gap: 10px; margin-top: 5px;">
                    <button class="btn btn-circle" style="width: 24px; height: 24px; font-size: 10px;"
                        onclick="changePeriod(-1)">-</button>
                    <button class="btn btn-circle" style="width: 24px; height: 24px; font-size: 10px;"
                        onclick="changePeriod(1)">+</button>
                </div>
            </div>

            <div class="timer-section">
                <div class="timer-display" id="timer" onclick="setTime()" title="Click to set time"
                    style="cursor: pointer;">12:00</div>
                <div class="timer-controls">
                    <button class="btn btn-timer" id="timerBtn" onclick="toggleTimer()">
                        <i class="fas fa-play"></i> START
                    </button>
                    <button class="btn" onclick="resetTimer()">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button class="btn btn-circle" style="width: 40px; height: 40px; font-size: 14px;"
                        onclick="setTime()">
                        <i class="fas fa-cog"></i>
                    </button>
                </div>
            </div>

            <div class="period-box">
                <span class="period-label">Fouls</span>
                <div style="display: flex; gap: 20px;">
                    <div class="text-center" style="display: flex; flex-direction: column; align-items: center;">
                        <span class="period-value" id="foul-home" style="color: var(--home-color)">0</span>
                        <span class="period-label" style="font-size: 8px;">HOME</span>
                    </div>
                    <div class="text-center" style="display: flex; flex-direction: column; align-items: center;">
                        <span class="period-value" id="foul-away" style="color: var(--away-color)">0</span>
                        <span class="period-label" style="font-size: 8px;">AWAY</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Board -->
        <div class="scoreboard-grid">
            <!-- Home Team -->
            <div class="team-card home">
                <input type="text" class="team-name" value="HOME TEAM" spellcheck="false">
                <div class="score-display" id="score-home">0</div>
                <div class="score-controls">
                    <button class="btn btn-circle" onclick="changeScore('home', -1)">-</button>
                    <button class="btn btn-circle btn-primary-glow" style="width: 80px; height: 80px;"
                        onclick="changeScore('home', 1)">+1</button>
                    <button class="btn btn-circle" onclick="changeScore('home', 2)">+2</button>
                    <button class="btn btn-circle" onclick="changeScore('home', 3)">+3</button>
                </div>
            </div>

            <!-- Away Team -->
            <div class="team-card away">
                <input type="text" class="team-name" value="AWAY TEAM" spellcheck="false">
                <div class="score-display" id="score-away">0</div>
                <div class="score-controls">
                    <button class="btn btn-circle" onclick="changeScore('away', -1)">-</button>
                    <button class="btn btn-circle btn-secondary-glow" style="width: 80px; height: 80px;"
                        onclick="changeScore('away', 1)">+1</button>
                    <button class="btn btn-circle" onclick="changeScore('away', 2)">+2</button>
                    <button class="btn btn-circle" onclick="changeScore('away', 3)">+3</button>
                </div>
            </div>
        </div>

        <!-- Extra Controls -->
        <div class="footer-controls">
            <button class="btn" onclick="changeFoul('home', 1)"><i class="fas fa-flag"></i> HOME FOUL</button>
            <button class="btn" onclick="resetBoard()"><i class="fas fa-trash"></i> RESET BOARD</button>
            <button class="btn" onclick="changeFoul('away', 1)"><i class="fas fa-flag"></i> AWAY FOUL</button>
        </div>
    </div>

    <!-- Hidden Settings Button -->
    <button class="btn btn-circle settings-btn" onclick="openFullscreen()">
        <i class="fas fa-expand"></i>
    </button>

    <script>
        let homeScore = 0;
        let awayScore = 0;
        let homeFouls = 0;
        let awayFouls = 0;
        let period = 1;

        let baseTime = 720; // 12 minutes base
        let timeLeft = baseTime;
        let isRunning = false;
        let timerId = null;

        // Sound Effects (Optional - adding simple ones via Audio Context if needed)
        const audioCtx = new(window.AudioContext || window.webkitAudioContext)();

        function playSound(freq, duration) {
            const oscillator = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            oscillator.connect(gain);
            gain.connect(audioCtx.destination);
            oscillator.frequency.value = freq;
            oscillator.type = 'square';
            gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);
            oscillator.start();
            oscillator.stop(audioCtx.currentTime + duration);
        }

        // Score Logic
        function changeScore(team, amount) {
            if (team === 'home') {
                homeScore = Math.max(0, homeScore + amount);
                document.getElementById('score-home').innerText = homeScore;
                animateScore('score-home');
                if (amount > 0) playSound(800, 0.1);
            } else {
                awayScore = Math.max(0, awayScore + amount);
                document.getElementById('score-away').innerText = awayScore;
                animateScore('score-away');
                if (amount > 0) playSound(1000, 0.1);
            }
        }

        function animateScore(id) {
            const el = document.getElementById(id);
            el.classList.remove('score-animate');
            void el.offsetWidth; // Trigger reflow
            el.classList.add('score-animate');
        }

        // Timer Logic
        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('timer').innerText =
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 10) {
                document.getElementById('timer').style.color = 'var(--away-color)';
            } else {
                document.getElementById('timer').style.color = '#fff';
            }
        }

        function toggleTimer() {
            const btn = document.getElementById('timerBtn');
            const display = document.getElementById('timer');

            if (isRunning) {
                clearInterval(timerId);
                btn.innerHTML = '<i class="fas fa-play"></i> START';
                btn.classList.remove('btn-secondary-glow');
                btn.classList.add('btn-timer');
                display.classList.remove('running');
                isRunning = false;
            } else {
                if (timeLeft <= 0) return;

                timerId = setInterval(() => {
                    timeLeft--;
                    updateTimerDisplay();
                    if (timeLeft <= 0) {
                        clearInterval(timerId);
                        isRunning = false;
                        btn.innerHTML = '<i class="fas fa-play"></i> START';
                        playSound(200, 1.5); // Buzzer sound
                        alert("TIME'S UP!");
                    }
                }, 1000);

                btn.innerHTML = '<i class="fas fa-pause"></i> PAUSE';
                btn.classList.remove('btn-timer');
                btn.classList.add('btn-secondary-glow');
                display.classList.add('running');
                isRunning = true;
            }
        }

        function resetTimer() {
            clearInterval(timerId);
            isRunning = false;
            timeLeft = baseTime;
            updateTimerDisplay();
            const btn = document.getElementById('timerBtn');
            btn.innerHTML = '<i class="fas fa-play"></i> START';
            btn.classList.remove('btn-secondary-glow');
            btn.classList.add('btn-timer');
            document.getElementById('timer').classList.remove('running');
        }

        function setTime() {
            const currentMinutes = Math.floor(baseTime / 60);
            const newMinutes = prompt("Enter game minutes:", currentMinutes);

            if (newMinutes !== null && !isNaN(newMinutes) && newMinutes >= 0) {
                baseTime = parseInt(newMinutes) * 60;
                resetTimer();
            }
        }

        // Stats Logic
        function changePeriod(amount) {
            period = Math.max(1, period + amount);
            document.getElementById('period').innerText = period;
        }

        function changeFoul(team, amount) {
            if (team === 'home') {
                homeFouls = Math.max(0, homeFouls + amount);
                document.getElementById('foul-home').innerText = homeFouls;
            } else {
                awayFouls = Math.max(0, awayFouls + amount);
                document.getElementById('foul-away').innerText = awayFouls;
            }
        }

        function resetBoard() {
            if (confirm('Reset all scores and stats?')) {
                homeScore = 0;
                awayScore = 0;
                homeFouls = 0;
                awayFouls = 0;
                period = 1;
                document.getElementById('score-home').innerText = 0;
                document.getElementById('score-away').innerText = 0;
                document.getElementById('foul-home').innerText = 0;
                document.getElementById('foul-away').innerText = 0;
                document.getElementById('period').innerText = 1;
                resetTimer();
            }
        }

        function openFullscreen() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                /* Safari */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                /* IE11 */
                elem.msRequestFullscreen();
            }
        }

        // Keyboard Shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT') return;

            switch (e.key.toLowerCase()) {
                case ' ':
                    toggleTimer();
                    e.preventDefault();
                    break;
                case 'h':
                    changeScore('home', 1);
                    break;
                case 'a':
                    changeScore('away', 1);
                    break;
                case 'r':
                    resetTimer();
                    break;
            }
        });

        // Initial Display
        updateTimerDisplay();
    </script>
</body>

</html>
