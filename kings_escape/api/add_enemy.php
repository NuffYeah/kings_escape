
<?php

/*** API endpoint to add enemypiecesduring gameplay

* Simulatesenemyinvasion mechanics

*/

header('Content-Type: application/json');

header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: POST, OPTIONS');

header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/Database.php';

require_once '../classes/Game.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

echo json_encode(['success' => false, 'message' => 'Method not allowed']);

exit;

}

try{

// Get JSON input with error handling
$rawInput = file_get_contents('php://input');
if ($rawInput === false) {
    throw new Exception('Failed to read request body');
}

$input = json_decode($rawInput, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('Invalid JSON: ' . json_last_error_msg());
}

if (!isset($input['game_id']) || !isset($input['piece_type']) ||!isset($input['x']) || !isset($input['y'])) {

throw new Exception('Missing required parameters');

}

$gameId = $input['game_id'];

$pieceType = $input['piece_type'];

$x= (int)$input['x'];

$y= (int)$input['y'];


// Validate piece type


$validPieces= ['Pawn', 'Knight', 'Bishop', 'Rook', 'Queen'];

if (!in_array($pieceType, $validPieces)) {

throw new Exception('Invalid piece type');

}


$game = new Game($gameId);

$session = $game->getGameSession();

if (!$session || $session['game_status'] !== 'active') {

throw new Exception('Game not found');

}

// Add enemy piece at current move + 1
$nextMove = $session['current_move'] + 1;

$success = $game->addPiece($nextMove, $pieceType, $x, $y, true);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Enemy piece added successfully']);
} else {
    throw new Exception('Failed to add enemy piece');
}

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);

}

?>
