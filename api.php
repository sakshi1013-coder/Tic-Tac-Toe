<?php
session_start();

header('Content-Type: application/json');

// Initialize game state if not exists
if (!isset($_SESSION['board'])) {
    resetGame();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'move':
        handleMove();
        break;
    case 'restart':
        resetGame(false); // Restart only board/turn
        break;
    case 'reset_scores':
        resetGame(true); // Reset everything including scores
        break;
    case 'update_names':
        updateNames();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function handleMove() {
    $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
    
    if ($_SESSION['status'] !== 'playing' || $index < 0 || $index > 8 || $_SESSION['board'][$index] !== '') {
        echo json_encode(getCurrentState());
        return;
    }

    $_SESSION['board'][$index] = $_SESSION['turn'];
    
    if (checkWin()) {
        $_SESSION['status'] = 'win';
        $_SESSION['winner'] = $_SESSION['turn'];
        $_SESSION['scores'][$_SESSION['turn']]++;
    } elseif (checkDraw()) {
        $_SESSION['status'] = 'draw';
        $_SESSION['scores']['Draws']++;
    } else {
        $_SESSION['turn'] = ($_SESSION['turn'] === 'X') ? 'O' : 'X';
    }

    echo json_encode(getCurrentState());
}

function checkWin() {
    $winningCombos = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8], // Rows
        [0, 3, 6], [1, 4, 7], [2, 5, 8], // Cols
        [0, 4, 8], [2, 4, 6]             // Diagonals
    ];

    foreach ($winningCombos as $combo) {
        if ($_SESSION['board'][$combo[0]] !== '' &&
            $_SESSION['board'][$combo[0]] === $_SESSION['board'][$combo[1]] &&
            $_SESSION['board'][$combo[1]] === $_SESSION['board'][$combo[2]]) {
            $_SESSION['win_combo'] = $combo;
            return true;
        }
    }
    return false;
}

function checkDraw() {
    foreach ($_SESSION['board'] as $cell) {
        if ($cell === '') return false;
    }
    return true;
}

function resetGame($all = false) {
    $_SESSION['board'] = array_fill(0, 9, '');
    $_SESSION['turn'] = 'X';
    $_SESSION['status'] = 'playing';
    $_SESSION['winner'] = null;
    $_SESSION['win_combo'] = null;
    
    if ($all) {
        $_SESSION['scores'] = ['X' => 0, 'O' => 0, 'Draws' => 0];
        $_SESSION['players'] = ['X' => 'Player 1', 'O' => 'Player 2'];
    }
    
    if (isset($_POST['action'])) {
        echo json_encode(getCurrentState());
    }
}

function updateNames() {
    $playerX = $_POST['playerX'] ?? 'Player 1';
    $playerO = $_POST['playerO'] ?? 'Player 2';
    $_SESSION['players']['X'] = $playerX;
    $_SESSION['players']['O'] = $playerO;
    echo json_encode(getCurrentState());
}

function getCurrentState() {
    return [
        'board' => $_SESSION['board'],
        'turn' => $_SESSION['turn'],
        'scores' => $_SESSION['scores'],
        'status' => $_SESSION['status'],
        'winner' => $_SESSION['winner'],
        'win_combo' => $_SESSION['win_combo'],
        'players' => $_SESSION['players']
    ];
}
?>
