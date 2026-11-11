
<?php

/*** API endpoint to make a king move

* Processesmove requestsand updatesgame state

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

if (!isset($input['game_id']) || !isset($input['x']) || !isset($input['y'])) {
    throw new Exception('Missing required parameters');

}$gameId = $input['game_id'];$x= (int)$input['x'];

$y= (int)$input['y'];

// Create game instance with existing game ID

$game = new Game($gameId);


// Get current game session to determine next move number

$session = $game->getGameSession();

if (!$session) {throw new Exception('Game not found');

}

if ($session['game_status'] !== 'active') {

throw new Exception('Game isnot active');

}

$nextMove = $session['current_move'] + 1;

$result = $game->moveKing($nextMove, $x, $y);

echo json_encode($result);

} catch (Exception $e) {
    http_response_code(400);

echo json_encode(['success' => false,'message' => $e->getMessage()]);

}

?>


