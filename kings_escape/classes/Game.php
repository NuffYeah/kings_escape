<?php

require_once '../config/Database.php';

// Define board size constant if not already defined
if (!defined('BOARD_SIZE')) {
    define('BOARD_SIZE', 8);
}

/*** Main game logicclass
* Handlesall game mechanics, moves, and win conditions
*/

class Game {
private $db;
private $gameId;

 // Database connection 
// Current game session ID

private $boardSize; 

// Size of the chessboard (8x8)


/*** Constructor initializesdatabase connection and game settings

* @param string $gameId Unique identifier for thisgame session

*/

public function __construct($gameId = null) {
    $this->db = Database::getInstance()->getConnection();
    $this->boardSize = BOARD_SIZE;
    $this->gameId = $gameId ?: $this->generateGameId();
}

/*** Generate a unique game ID
* @return string Unique game identifier
*/
private function generateGameId() {
    return 'game_' . uniqid() . '_' . time();
}


/*** Start a new game with specified parameters

* @param int $startX King'sstarting X position
* @param int $startY King'sstarting Y position
* @param int $exitX Exit point X position
* @param int $exitY Exit point Y position
* @return arrayGame initialization result
*/
public function startNewGame($startX, $startY, $exitX, $exitY) {

try{ // Validate input coordinates

if (
!$this->isValidPosition($startX, $startY) ||
!$this->isValidPosition($exitX, $exitY)) {
throw new Exception("Invalid starting or exit positions");

}

// Start database transaction for data consistency
$this->db->beginTransaction();

// Create new game session record

$stmt = $this->db->prepare("INSERT INTO game_sessions (game_id, king_start_x, king_start_y, exit_x, exit_y) VALUES (?, ?, ?, ?, ?)");

$stmt->execute([$this->gameId, $startX, $startY, $exitX, $exitY]);

// Place the king at starting position

$this->addPiece(0, 'King', $startX, $startY, false);

// Commit transaction

$this->db->commit();

return [
    'success' => true,
    'game_id' => $this->gameId,
    'message' => 'Game started successfully'
];

} catch (Exception $e) {
    // Rollback transaction on error
    $this->db->rollback();
    return [
        'success' => false,
        'message' => 'Failed to start game: ' . $e->getMessage()
    ];
}}

/*** Add a piece to the board at specified move number

* @param int $moveNumber Which move thispiece appears

* @param string $pieceType Type of chesspiece

* @param int $xX coordinate

* @param int $yY coordinate

* @param bool $isEnemyWhether thisisan enemypiece

* @return bool Successstatus

*/


public function addPiece($moveNumber, $pieceType, $x, $y, $isEnemy = true) {
    try {
        $stmt = $this->db->prepare("INSERT INTO chessboard_positions (game_id, move_number, piece_type, position_x, position_y, is_enemy) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$this->gameId, $moveNumber, $pieceType, $x, $y, $isEnemy ? 1 : 0]);
    } catch (PDOException $e) {
        error_log("Failed to add piece: " . $e->getMessage());
        return false;
    }
}


/*** Checkif a position isvalid (within board boundaries)

* @param int $xX coordinate

* @param int $yY coordinate
* @return bool True if position isvalid

*/

private function isValidPosition($x, $y) {
    return $x >= 0 && $x < $this->boardSize && $y >= 0 && $y < $this->boardSize;
}

/*** Get all occupied positionsat a specificmove number
* @param int $moveNumber Move to check

* @return arrayArrayof occupied positions

*/

public function getOccupiedPositions($moveNumber) {
    $stmt = $this->db->prepare("
        SELECT cp1.position_x, cp1.position_y, cp1.piece_type, cp1.is_enemy
        FROM chessboard_positions cp1
        INNER JOIN (
            SELECT position_x, position_y, MAX(move_number) as max_move
            FROM chessboard_positions
            WHERE game_id = ? AND move_number <= ?
            GROUP BY position_x, position_y
        ) cp2 ON cp1.position_x = cp2.position_x 
               AND cp1.position_y = cp2.position_y 
               AND cp1.move_number = cp2.max_move
        WHERE cp1.game_id = ?
    ");

    $stmt->execute([$this->gameId, $moveNumber, $this->gameId]);
    return $stmt->fetchAll();
}


/*** Get current king position at specificmove

* @param int $moveNumber Move to check

* @return array|null King position or null if not found

*/

public function getKingPosition($moveNumber) {
    $stmt = $this->db->prepare("
        SELECT position_x, position_y
        FROM chessboard_positions
        WHERE game_id = ? AND piece_type = 'King' AND move_number <= ?
        ORDER BY move_number DESC
        LIMIT 1
    ");

    $stmt->execute([$this->gameId, $moveNumber]);
    return $stmt->fetch();
}

/*** Get all possible movesfor the king from current position

* @param int $kingX Current king X position
* @param int $kingY Current king Y position
* @param array$occupiedPositionsCurrentlyoccupied positions
* @return arrayArrayof valid move coordinates
*/

public function getPossibleMoves($kingX, $kingY, $occupiedPositions) {
    $moves= [];
    
    // King can move one square in anydirection (8 possible moves)

$directions= [
    [-1, -1], [-1, 0], [-1, 1], // Up-left, Up, Up-right
    [0, -1], [0, 1], // Left, Right
    [1, -1], [1, 0], [1, 1] // Down-left, Down, Down-right
];

foreach ($directions as $direction) {
    
    $newX = $kingX + $direction[0];

$newY = $kingY + $direction[1];

// Checkif new position isvalid and unoccupied

if ($this->isValidPosition($newX, $newY)) {
    $isOccupied = false;
    foreach ($occupiedPositions as $pos) {
        if ($pos['position_x'] == $newX && $pos['position_y'] == $newY) {
            $isOccupied = true;
            break;
        }
    }
    if (!$isOccupied) {
        $moves[] = ['x' => $newX, 'y' => $newY];
    }
}
}

return $moves;

}

/*** Move the king to a new position
* @param int $moveNumber Current move number
* @param int $newX New X position
* @param int $newY New Y position
* @return arrayMove result
*/

public function moveKing($moveNumber, $newX, $newY) {
    
    try{
         // Get current king position

        $currentKing = $this->getKingPosition($moveNumber - 1);

        if (!$currentKing) {
            throw new Exception("King position not found");

}

// Get occupied positions

$occupied = $this->getOccupiedPositions($moveNumber);

// Checkif move isvalid


$possibleMoves= $this->getPossibleMoves(
    $currentKing['position_x'],
    $currentKing['position_y'],$occupied);


$validMove = false;

foreach ($possibleMoves as $move) {
    if ($move['x'] == $newX && $move['y'] == $newY) {
        $validMove = true;
        break;

}
}
if (!$validMove) {
    throw new Exception("Invalid move");

}
        // Add king's new position
$this->addPiece($moveNumber, 'King', $newX, $newY, false);

// Update game session
$stmt = $this->db->prepare("UPDATE game_sessions SET current_move = ? WHERE game_id = ?");
$stmt->execute([$moveNumber, $this->gameId]);

// Check if king reached exit
$gameSession = $this->getGameSession();
if ($newX == $gameSession['exit_x'] && $newY == $gameSession['exit_y']) {
    $this->updateGameStatus('won');
    return [
        'success' => true,
        'won' => true,
        'message' => 'Congratulations! The King has escaped!'
    ];
}

// Spawn enemies after successful king move
$this->spawnEnemies($moveNumber);

return [
    'success' => true,
    'won' => false,
    'message' => 'Move successful'
];

} catch (Exception $e) {
    return [
        'success' => false,
        'message' => $e->getMessage()
    ];

}

}

/*** Spawn enemies automatically after king moves
* @param int $moveNumber Current move number
* @return void
*/
private function spawnEnemies($moveNumber) {
    // Get current occupied positions to avoid spawning on occupied squares
    $occupied = $this->getOccupiedPositions($moveNumber);
    
    // Create a lookup array for faster position checking
    $occupiedPositions = [];
    foreach ($occupied as $pos) {
        $occupiedPositions[$pos['position_x'] . ',' . $pos['position_y']] = true;
    }
    
    // Enemy spawn probability increases with move number
    $spawnChance = min(0.3 + ($moveNumber * 0.05), 0.8); // 30% base, +5% per move, max 80%
    
    // Try to spawn 1-2 enemies per move
    $maxEnemies = ($moveNumber > 5) ? 2 : 1;
    $enemiesSpawned = 0;
    
    $enemyTypes = ['Pawn', 'Knight', 'Bishop', 'Rook'];
    
    for ($attempt = 0; $attempt < 20 && $enemiesSpawned < $maxEnemies; $attempt++) {
        if (rand(1, 100) / 100 > $spawnChance) {
            continue; // Skip this spawn attempt
        }
        
        // Random position on the board
        $x = rand(0, $this->boardSize - 1);
        $y = rand(0, $this->boardSize - 1);
        
        // Don't spawn on occupied positions
        if (isset($occupiedPositions[$x . ',' . $y])) {
            continue;
        }
        
        // Choose random enemy type (weighted toward weaker pieces early game)
        $enemyType = $enemyTypes[rand(0, min(count($enemyTypes) - 1, $moveNumber < 5 ? 1 : 3))];
        
        // Add the enemy piece
        if ($this->addPiece($moveNumber, $enemyType, $x, $y, true)) {
            $occupiedPositions[$x . ',' . $y] = true; // Mark as occupied for next spawn
            $enemiesSpawned++;
        }
    }
}

/*** Get current game session data

* @return arrayGame session information

*/

public function getGameSession() {
    $stmt = $this->db->prepare("SELECT * FROM game_sessions WHERE game_id = ?");
    $stmt->execute([$this->gameId]);
    return $stmt->fetch();
}
/*** Update game status(active, won, lost)

* @param string $statusNew game status
* @return bool Successstatus
*/

public function updateGameStatus($status) {
    $stmt = $this->db->prepare("UPDATE game_sessions SET game_status = ? WHERE game_id = ?");

return $stmt->execute([$status, $this->gameId]);

}
/*** Get current game state for display


* @return arrayComplete game state

*/

public function getGameState() {
    $session = $this->getGameSession();
    
    if (!$session) {
        return ['error' => 'Game session not found'];
    }
    
    $currentMove = $session['current_move'];
    $occupied = $this->getOccupiedPositions($currentMove);
    $kingPosition = $this->getKingPosition($currentMove);
    
    // Get possible moves for current king position
    $possibleMoves = [];
    if ($kingPosition) {
        $possibleMoves = $this->getPossibleMoves(
            $kingPosition['position_x'],
            $kingPosition['position_y'],
            $occupied
        );
    }
    
    return [
        'game_id' => $this->gameId,
        'current_move' => $currentMove,
        'game_status' => $session['game_status'],
        'king_position' => $kingPosition,
        'exit_position' => ['x' => $session['exit_x'], 'y' => $session['exit_y']],
        'occupied_positions' => $occupied,
        'possible_moves' => $possibleMoves
    ];

}
}
?>
