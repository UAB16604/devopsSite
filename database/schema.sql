CREATE DATABASE IF NOT EXISTS tekken_tournament;
USE tekken_tournament;

CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gamer_tag VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    country VARCHAR(100) NOT NULL,
    main_character VARCHAR(50) NOT NULL,
    registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    round_name VARCHAR(50) NOT NULL,
    player1_id INT NOT NULL,
    player2_id INT NOT NULL,
    match_time DATETIME NOT NULL,
    status ENUM('scheduled', 'completed') NOT NULL DEFAULT 'scheduled',
    winner_id INT NULL,
    score VARCHAR(20) NULL,
    FOREIGN KEY (player1_id) REFERENCES players(id),
    FOREIGN KEY (player2_id) REFERENCES players(id),
    FOREIGN KEY (winner_id) REFERENCES players(id)
);

INSERT INTO players (gamer_tag, full_name, email, country, main_character) VALUES
('KingOfIronFist', 'James Carter', 'james@example.com', 'USA', 'Jin'),
('SteelPhoenix', 'Maria Santos', 'maria@example.com', 'Spain', 'King'),
('ThunderBear', 'Kenji Sato', 'kenji@example.com', 'Japan', 'Paul'),
('NightCombo', 'Aisha Khan', 'aisha@example.com', 'Pakistan', 'Ling Xiaoyu');

INSERT INTO matches (round_name, player1_id, player2_id, match_time, status, winner_id, score) VALUES
('Semi Final 1', 1, 2, '2026-11-21 12:00:00', 'completed', 1, '2-1'),
('Semi Final 2', 3, 4, '2026-11-21 13:00:00', 'completed', 3, '2-0'),
('Final', 1, 3, '2026-11-21 15:00:00', 'scheduled', NULL, NULL);
