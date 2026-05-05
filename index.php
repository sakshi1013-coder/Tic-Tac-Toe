<?php
session_start();

// Initialize game state if not exists
if (!isset($_SESSION['board'])) {
    $_SESSION['board'] = array_fill(0, 9, '');
    $_SESSION['turn'] = 'X';
    $_SESSION['scores'] = ['X' => 0, 'O' => 0, 'Draws' => 0];
    $_SESSION['status'] = 'playing'; // playing, win, draw
    $_SESSION['winner'] = null;
    $_SESSION['win_combo'] = null;
    $_SESSION['players'] = ['X' => 'Player 1', 'O' => 'Player 2'];
}

// Get current state for JS
$gameState = [
    'board' => $_SESSION['board'],
    'turn' => $_SESSION['turn'],
    'scores' => $_SESSION['scores'],
    'status' => $_SESSION['status'],
    'winner' => $_SESSION['winner'],
    'win_combo' => $_SESSION['win_combo'],
    'players' => $_SESSION['players']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tic Tac Toe | Pastel Modern</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
</head>
<body class="light-mode">
    <div class="app-container">
        <header>
            <h1>Tic Tac Toe</h1>
            <div class="theme-toggle">
                <label class="switch">
                    <input type="checkbox" id="dark-mode-toggle">
                    <span class="slider round"></span>
                </label>
                <span class="mode-label">Dark Mode</span>
            </div>
        </header>

        <main>
            <div class="game-info">
                <div class="player-inputs">
                    <div class="input-group">
                        <span class="symbol x">X</span>
                        <input type="text" id="player-x-name" value="<?php echo htmlspecialchars($_SESSION['players']['X']); ?>" placeholder="Player 1" onfocus="this.select()">
                    </div>
                    <div class="input-group">
                        <span class="symbol o">O</span>
                        <input type="text" id="player-o-name" value="<?php echo htmlspecialchars($_SESSION['players']['O']); ?>" placeholder="Player 2" onfocus="this.select()">
                    </div>
                </div>

                <div class="status-banner" id="status-banner">
                    <span id="current-turn-text"><?php echo $_SESSION['players'][$_SESSION['turn']]; ?>'s Turn</span>
                </div>

                <div class="score-board">
                    <div class="score-card x">
                        <span class="label">X Wins</span>
                        <span class="value" id="score-x"><?php echo $_SESSION['scores']['X']; ?></span>
                    </div>
                    <div class="score-card draws">
                        <span class="label">Draws</span>
                        <span class="value" id="score-draws"><?php echo $_SESSION['scores']['Draws']; ?></span>
                    </div>
                    <div class="score-card o">
                        <span class="label">O Wins</span>
                        <span class="value" id="score-o"><?php echo $_SESSION['scores']['O']; ?></span>
                    </div>
                </div>
            </div>

            <div class="game-board-container">
                <div class="game-board" id="game-board">
                    <?php for ($i = 0; $i < 9; $i++): ?>
                        <div class="cell" data-index="<?php echo $i; ?>">
                            <span class="cell-content"><?php echo $_SESSION['board'][$i]; ?></span>
                        </div>
                    <?php endfor; ?>
                    <div id="winning-line" class="winning-line"></div>
                </div>
                <div id="confetti-container"></div>
            </div>

            <div class="controls">
                <button id="restart-btn" class="btn primary">Restart Game</button>
                <button id="reset-scores-btn" class="btn secondary">Reset Scores</button>
            </div>
        </main>

        <footer>
            <p>Built with ❤️ & PHP</p>
        </footer>
    </div>

    <!-- Modal for game end -->
    <div id="result-modal" class="modal">
        <div class="modal-content">
            <h2 id="result-title">Winner!</h2>
            <p id="result-message">Congratulations, Player 1!</p>
            <button id="modal-close" class="btn primary">Play Again</button>
        </div>
    </div>

    <script>
        // Pass PHP state to JS
        window.initialGameState = <?php echo json_encode($gameState); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
