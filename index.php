<?php
session_start();

// Инициализация счета и игрового поля
if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = ['X' => 0, 'O' => 0, 'draw' => 0];
}

if (!isset($_SESSION['board']) || isset($_GET['reset'])) {
    $_SESSION['board'] = array_fill(0, 9, '');
    $_SESSION['currentPlayer'] = 'X';
    $_SESSION['gameOver'] = false;
    $_SESSION['winner'] = null;
}

// Обработка хода
if (isset($_GET['cell']) && !$_SESSION['gameOver']) {
    $cell = (int)$_GET['cell'];
    
    if ($_SESSION['board'][$cell] === '') {
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
    }
}

// Функция проверки победы
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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Крестики-нолики</title>
    <style>
        :root {
            --dark-bg: #121212;
            --dark-cell: #1e1e1e;
            --accent-x: #ff5252;
            --accent-o: #448aff;
            --text: #e0e0e0;
            --border: #333;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 500px;
            width: 100%;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }
        
        .score-board {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .score-item {
            text-align: center;
            padding: 10px;
        }
        
        .score-value {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .score-x { color: var(--accent-x); }
        .score-o { color: var(--accent-o); }
        .score-draw { color: var(--text); }
        
        .game-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.2rem;
            min-height: 30px;
        }
        
        .current-player {
            font-weight: bold;
            text-shadow: 0 0 8px currentColor;
        }
        
        .current-player.x { color: var(--accent-x); }
        .current-player.o { color: var(--accent-o); }
        
        .game-board {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .cell {
            aspect-ratio: 1;
            background-color: var(--dark-cell);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .cell:hover:not(.disabled) {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .cell.x { color: var(--accent-x); }
        .cell.o { color: var(--accent-o); }
        
        .disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }
        
        .winning-cell {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.3); }
            70% { box-shadow: 0 0 0 10px rgba(255, 255, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }
        
        .controls {
            display: flex;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 24px;
            background: linear-gradient(45deg, var(--accent-x), var(--accent-o));
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        .message {
            text-align: center;
            margin-top: 20px;
            font-size: 1.2rem;
            min-height: 30px;
        }
        
        .winner-x { color: var(--accent-x); }
        .winner-o { color: var(--accent-o); }
        
        @media (max-width: 500px) {
            h1 { font-size: 2rem; }
            .score-value { font-size: 1.5rem; }
            .cell { font-size: 2.5rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Крестики-нолики</h1>
        
        <div class="score-board">
            <div class="score-item">
                <div>Крестики</div>
                <div class="score-value score-x"><?= $_SESSION['score']['X'] ?></div>
            </div>
            <div class="score-item">
                <div>Ничьи</div>
                <div class="score-value score-draw"><?= $_SESSION['score']['draw'] ?></div>
            </div>
            <div class="score-item">
                <div>Нолики</div>
                <div class="score-value score-o"><?= $_SESSION['score']['O'] ?></div>
            </div>
        </div>
        
        <div class="game-info">
            <?php if (!$_SESSION['gameOver']): ?>
                <span>Сейчас ходит: </span>
                <span class="current-player <?= $_SESSION['currentPlayer'] === 'X' ? 'x' : 'o' ?>">
                    <?= $_SESSION['currentPlayer'] === 'X' ? 'Крестики' : 'Нолики' ?>
                </span>
            <?php endif; ?>
        </div>
        
        <div class="game-board">
            <?php for ($i = 0; $i < 9; $i++): ?>
                <?php
                $class = '';
                if ($_SESSION['board'][$i] !== '') {
                    $class = $_SESSION['board'][$i] === 'X' ? 'x' : 'o';
                }
                if ($_SESSION['gameOver']) {
                    $class .= ' disabled';
                }
                ?>
                <a href="?cell=<?= $i ?>" class="cell <?= $class ?>">
                    <?= $_SESSION['board'][$i] ?>
                </a>
            <?php endfor; ?>
        </div>
        
        <div class="controls">
            <a href="?reset=1" class="btn">Новая игра</a>
        </div>
        
        <div class="message">
            <?php if ($_SESSION['gameOver']): ?>
                <?php if ($_SESSION['winner'] === 'draw'): ?>
                    <span>Ничья!</span>
                <?php else: ?>
                    <span class="winner-<?= strtolower($_SESSION['winner']) ?>">
                        Победили <?= $_SESSION['winner'] === 'X' ? 'Крестики' : 'Нолики' ?>!
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
