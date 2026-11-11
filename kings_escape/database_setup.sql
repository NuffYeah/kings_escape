-- Kings Escape Game Database Setup
-- Run this SQL script to create the required database and tables

CREATE DATABASE IF NOT EXISTS kings_escape;
USE kings_escape;

-- Game sessions table to track each game instance
CREATE TABLE IF NOT EXISTS game_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id VARCHAR(255) UNIQUE NOT NULL,
    king_start_x INT NOT NULL,
    king_start_y INT NOT NULL,
    exit_x INT NOT NULL,
    exit_y INT NOT NULL,
    current_move INT DEFAULT 0,
    game_status ENUM('active', 'won', 'lost') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Chessboard positions table to track all pieces and their positions
CREATE TABLE IF NOT EXISTS chessboard_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id VARCHAR(255) NOT NULL,
    move_number INT NOT NULL,
    piece_type VARCHAR(50) NOT NULL,
    position_x INT NOT NULL,
    position_y INT NOT NULL,
    is_enemy BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES game_sessions(game_id) ON DELETE CASCADE,
    INDEX idx_game_move (game_id, move_number),
    INDEX idx_position (position_x, position_y)
);
