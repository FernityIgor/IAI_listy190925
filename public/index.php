<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Clear any opcode cache
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Debug: Log at the very start of index.php
$timestamp = date('Y-m-d H:i:s');
error_log("[$timestamp] INDEX.PHP STARTED - Method: " . $_SERVER['REQUEST_METHOD']);

// 1. Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';
error_log("[$timestamp] Autoloader included");

// 2. Use the controller we created
use App\Controller\OrderController;

// 3. Create an instance of the controller
error_log("[$timestamp] Creating OrderController...");
$controller = new OrderController();
error_log("[$timestamp] OrderController created, calling show()...");

// Debug: Check if the method exists and what class we're actually dealing with
$reflection = new ReflectionClass($controller);
error_log("[$timestamp] Controller class: " . $reflection->getName());
error_log("[$timestamp] Controller file: " . $reflection->getFileName());
error_log("[$timestamp] Has show method: " . ($reflection->hasMethod('show') ? 'YES' : 'NO'));

// 4. Call the method that handles the request and shows the page
$controller->show();
error_log("[$timestamp] show() method completed");