<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// 1. Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Use the controller we created
use App\Controller\OrderController;

// 3. Create an instance of the controller
$controller = new OrderController();

// 4. Call the method that handles the request and shows the page
$controller->show();