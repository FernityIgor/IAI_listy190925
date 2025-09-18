<?php
// Simple test file to debug POST requests
error_log('DEBUG_POST: Request received at ' . date('Y-m-d H:i:s'));
error_log('DEBUG_POST: REQUEST METHOD: ' . $_SERVER['REQUEST_METHOD']);
error_log('DEBUG_POST: POST data: ' . print_r($_POST, true));
error_log('DEBUG_POST: Raw body: ' . file_get_contents('php://input'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'POST request received successfully',
        'post_data' => $_POST,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Not a POST request',
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
}