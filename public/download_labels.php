<?php
// Standalone handler for download_labels

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'download_labels') {
    // Parse parameters
    $orderNumber = (int)$_POST['order_number'];
    
    if (!$orderNumber) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid order number']);
        exit;
    }
    
    // Include necessary files for API client
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Load config
    $config = require __DIR__ . '/../config/config.php';
    
    // Create API client
    $apiClient = new App\Api\OrderApiClient(
        $config['api']['url'],
        $config['api']['key'],
        $config
    );
    
    // Download the labels
    $result = $apiClient->downloadLabels($orderNumber);
    
    // Return JSON response
    header('Content-Type: application/json');
    if ($result !== null) {
        echo json_encode([
            'success' => $result['success'] ?? false,
            'result' => $result
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'API call failed'
        ]);
    }
    exit;
}

// If not download_labels request, return error
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>