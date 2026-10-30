<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $sql = "SELECT p.id, p.gamer_tag, p.full_name,
                   SUM(CASE WHEN m.id IS NOT NULL THEN 1 ELSE 0 END) AS matches_played,
                   SUM(CASE WHEN m.winner_id = p.id THEN 1 ELSE 0 END) AS wins
            FROM players p
            LEFT JOIN matches m ON p.id = m.player1_id OR p.id = m.player2_id
            GROUP BY p.id, p.gamer_tag, p.full_name
            ORDER BY wins DESC, matches_played DESC, p.gamer_tag ASC";
    $statement = $pdo->query($sql);
    echo json_encode($statement->fetchAll());
} catch (PDOException $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not load results']);
}
