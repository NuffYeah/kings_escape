
<?php

/*** API endpoint to get current game state

* Returnscomplete game information for display

*/

header('Content-Type: application/json');

header('Access-Control-Allow-Origin: *');
require_once '../config/Database.php';
require_once '../classes/Game.php';

try{

if (!isset($_GET['game_id'])) {throw new Exception('Game ID required');

}

$gameId = $_GET['game_id'];

$game = new Game($gameId);

$gameState = $game->getGameState();

echo json_encode($gameState);

} catch (Exception $e) {http_response_code(400);

echo json_encode(['error' => $e->getMessage()]);

}

?>
