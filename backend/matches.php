<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $sql = 'SELECT m.id, m.round_name, m.match_time, m.winner_id, m.score,
                   p1.id AS player1_id, p1.gamer_tag AS player1,
                   p2.id AS player2_id, p2.gamer_tag AS player2,
                   pw.gamer_tag AS winner
            FROM matches m
            JOIN players p1 ON p1.id = m.player1_id
            JOIN players p2 ON p2.id = m.player2_id
            LEFT JOIN players pw ON pw.id = m.winner_id
            ORDER BY m.match_time ASC';
    $statement = $pdo->query($sql);
    echo json_encode($statement->fetchAll());
} catch (PDOException $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not load matches']);
}
