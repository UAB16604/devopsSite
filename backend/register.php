<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$gamerTag = trim($_POST['gamer_tag'] ?? '');
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$country = trim($_POST['country'] ?? '');
$mainCharacter = trim($_POST['main_character'] ?? '');

if ($gamerTag === '' || $fullName === '' || $email === '' || $country === '' || $mainCharacter === '') {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

try {
    $check = $pdo->prepare('SELECT id FROM players WHERE LOWER(gamer_tag) = LOWER(?) OR LOWER(email) = LOWER(?) LIMIT 1');
    $check->execute([$gamerTag, $email]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Gamer tag or email already exists']);
        exit;
    }

    $statement = $pdo->prepare('INSERT INTO players (gamer_tag, full_name, email, country, main_character) VALUES (?, ?, ?, ?, ?)');
    $statement->execute([$gamerTag, $fullName, $email, $country, $mainCharacter]);
    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (PDOException $error) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not register player']);
}
