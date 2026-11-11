<?php

/*** 
Database connection classusing PDO (PHP Data Objects)
* PDO ismore secure than mysqli asit preventsSQL injection better

*/

require_once __DIR__ . '/login.php'; 

class Database {
private static $instance = null;

private $connection;

 // PDO connection object

/*** Private constructor preventsdirect instantiation
* Thisensuresonlyone database connection exists(Singleton pattern)
*/

private function __construct() {
try{
    global $attr, $user, $pass, $opts;

$this->connection = new PDO($attr, $user, $pass, $opts);

} catch (PDOException $e) {

// Log error and show user-friendlymessage

error_log("Database connection failed: " . $e->getMessage());

header('Content-Type: application/json');
http_response_code(500);
echo json_encode(['error' => 'Database connection failed. Please try again later.']);
exit;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    private function __clone() {}
    public function __wakeup() {}
}