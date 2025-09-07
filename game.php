<?php
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Инициализация игры
function initGame() {
    $_SESSION['board'] = array_fill(0, 9, '');
    $_SESSION['currentPlayer'] = 'X';
    $_SESSION['gameOver'] = false;
    $_SESSION['winner'] = null;
    
    if (!isset($_SESSION['score'])) {
        $_SESSION['score'] = ['X' => 0, 'O' => 0, 'draw' => 0];
    }
}

// Проверка победы
function checkWin($board, $player) {
    $winConditions = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8], // Горизонтальные
        [0, 3, 6], [1, 4, 7], [2, 5, 8], // Вертикальные
        [0, 4, 8], [2, 4, 6]             // Диагональные
    ];
    
    foreach ($winConditions as $condition) {
        if ($board[$condition[0]] === $player && 
            $board[$condition[1]] === $player && 
            $board[$condition[2]] === $player) {
            return true;
        }
    }
    
    return false;
}

// Обработка действий
$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? ($input['action'] ?? '');

if ($action === 'reset') {
    initGame();
    
    echo json_encode([
        'success' => true,
        'board' => $_SESSION['board'],
        'currentPlayer' => $_SESSION['currentPlayer'],
        'gameOver' => $_SESSION['gameOver'],
        'score' => $_SESSION['score']
    ]);
    
} elseif ($action === 'load') {
    if (!isset($_SESSION['board'])) {
        initGame();
    }
    
    echo json_encode([
        'success' => true,
        'board' => $_SESSION['board'],
        'currentPlayer' => $_SESSION['currentPlayer'],
        'gameOver' => $_SESSION['gameOver'],
        'winner' => $_SESSION['winner'],
        'score' => $_SESSION['score']
    ]);
    
} elseif ($action === 'move') {
    if (!isset($_SESSION['board'])) {
        initGame();
    }
    
    $cell = (int)($input['cell'] ?? -1);
    $response = [];
    
    if ($_SESSION['gameOver']) {
        $response = [
            'success' => false,
            'message' => 'Игра уже завершена'
        ];
    } elseif ($cell < 0 || $cell > 8) {
        $response = [
            'success' => false,
            'message' => 'Неверный номер ячейки'
        ];
    } elseif ($_SESSION['board'][$cell] !== '') {
        $response = [
            'success' => false,
            'message' => 'Ячейка уже занята'
        ];
    } else {
        $_SESSION['board'][$cell] = $_SESSION['currentPlayer'];
        
        // Проверка на победу
        if (checkWin($_SESSION['board'], $_SESSION['currentPlayer'])) {
            $_SESSION['gameOver'] = true;
            $_SESSION['winner'] = $_SESSION['currentPlayer'];
            $_SESSION['score'][$_SESSION['currentPlayer']]++;
        } 
        // Проверка на ничью
        elseif (!in_array('', $_SESSION['board'])) {
            $_SESSION['gameOver'] = true;
            $_SESSION['winner'] = 'draw';
            $_SESSION['score']['draw']++;
        } 
        // Смена игрока
        else {
            $_SESSION['currentPlayer'] = $_SESSION['currentPlayer'] === 'X' ? 'O' : 'X';
        }
        
        $response = [
            'success' => true,
            'board' => $_SESSION['board'],
            'currentPlayer' => $_SESSION['currentPlayer'],
            'gameOver' => $_SESSION['gameOver'],
            'winner' => $_SESSION['winner'],
            'score' => $_SESSION['score']
        ];
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Неизвестное действие'
    ]);
}
?>
