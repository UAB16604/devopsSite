<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $statement = $pdo->query('SELECT id, gamer_tag, full_name, email, country, main_character FROM players ORDER BY gamer_tag');
    echo json_encode($statement->fetchAll());
} catch (PDOException $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not load players']);
}
