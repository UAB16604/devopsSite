<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$password = $_POST['password'] ?? '';
$adminPassword = getenv('ADMIN_PASSWORD') ?: 'admin123';
if (!hash_equals($adminPassword, $password)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid password']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'list_players') {
        $statement = $pdo->query('SELECT id, gamer_tag FROM players ORDER BY gamer_tag');
        echo json_encode(['success' => true, 'players' => $statement->fetchAll()]);
        exit;
    }

    if ($action === 'add_match') {
        $roundName = trim($_POST['round_name'] ?? '');
        $player1Id = filter_input(INPUT_POST, 'player1_id', FILTER_VALIDATE_INT);
        $player2Id = filter_input(INPUT_POST, 'player2_id', FILTER_VALIDATE_INT);
        $matchTime = trim($_POST['match_time'] ?? '');

        if ($roundName === '' || !$player1Id || !$player2Id || $matchTime === '' || $player1Id === $player2Id) {
            http_response_code(400);
            echo json_encode(['error' => 'Enter a round, two different players, and a match time']);
            exit;
        }

        $statement = $pdo->prepare('INSERT INTO matches (round_name, player1_id, player2_id, match_time) VALUES (?, ?, ?, ?)');
        $statement->execute([$roundName, $player1Id, $player2Id, $matchTime]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        exit;
    }

    if ($action === 'set_result') {
        $matchId = filter_input(INPUT_POST, 'match_id', FILTER_VALIDATE_INT);
        $winnerId = filter_input(INPUT_POST, 'winner_id', FILTER_VALIDATE_INT);
        $score = trim($_POST['score'] ?? '');

        if (!$matchId || !$winnerId || $score === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Enter a match, winner, and score']);
            exit;
        }

        $check = $pdo->prepare('SELECT player1_id, player2_id FROM matches WHERE id = ?');
        $check->execute([$matchId]);
        $match = $check->fetch();
        if (!$match || ($winnerId !== (int)$match['player1_id'] && $winnerId !== (int)$match['player2_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Winner must be one of the match players']);
            exit;
        }

        $statement = $pdo->prepare('UPDATE matches SET winner_id = ?, score = ? WHERE id = ?');
        $statement->execute([$winnerId, $score, $matchId]);
        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Unknown action']);
} catch (PDOException $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Admin operation failed']);
}
