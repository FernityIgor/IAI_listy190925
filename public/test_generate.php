<?php
// Test script to debug generate_labels issues
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    // Check if POST data exists
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Not a POST request', 'method' => $_SERVER['REQUEST_METHOD']]);
        exit;
    }
    
    if (!isset($_POST['action'])) {
        echo json_encode(['success' => false, 'error' => 'No action parameter', 'post_data' => array_keys($_POST)]);
        exit;
    }
    
    if ($_POST['action'] !== 'generate_labels') {
        echo json_encode(['success' => false, 'error' => 'Wrong action', 'action' => $_POST['action']]);
        exit;
    }
    
    // Test if vendor/autoload.php exists
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        echo json_encode(['success' => false, 'error' => 'autoload.php not found', 'path' => $autoloadPath]);
        exit;
    }
    
    // Test if config exists
    $configPath = __DIR__ . '/../config/config.php';
    if (!file_exists($configPath)) {
        echo json_encode(['success' => false, 'error' => 'config.php not found', 'path' => $configPath]);
        exit;
    }
    
    // Try to include autoload
    require_once $autoloadPath;
    
    // Try to load config
    $config = require $configPath;
    
    // If we get here, basic includes work
    echo json_encode([
        'success' => true, 
        'message' => 'Basic includes work',
        'order_id' => $_POST['order_id'] ?? 'not set',
        'config_loaded' => isset($config)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Exception: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Fatal Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>