<?php

/*** API endpoint to start a new game


* HandlesPOST requestswith game parameters
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try{

// Get JSON input

$input = json_decode(file_get_contents('php://input'), true);


// Validate required parameters


if (!isset($input['startX']) ||!isset($input['startY']) ||
!isset($input['exitX']) || !isset($input['exitY'])) {

throw new Exception('Missing required parameters');

}

// Validate coordinate ranges

$startX = (int)$input['startX'];

$startY = (int)$input['startY'];

$exitX = (int)$input['exitX'];

$exitY = (int)$input['exitY'];

if ($startX < 0 || $startX >= BOARD_SIZE || $startY < 0 || $startY >= BOARD_SIZE ||$exitX < 0 || $exitX >= BOARD_SIZE || $exitY < 0 || $exitY >= BOARD_SIZE) {

throw new Exception('Invalid coordinates');

}

// Create new game instance

$game = new Game();

$result = $game->startNewGame($startX, $startY, $exitX, $exitY);

echo json_encode($result);

} catch (Exception $e) {
    http_response_code(400);

echo json_encode(['success' => false,'message' => $e->getMessage()]);

}

?>

