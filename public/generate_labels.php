<?php
// Simple standalone handler for generate_labels
error_log("STANDALONE HANDLER CALLED");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_labels') {
    error_log("GENERATE LABELS STANDALONE - Order ID: " . $_POST['order_id']);
    error_log("GENERATE LABELS STANDALONE - Parameters: " . $_POST['parameters']);
    
    // Parse parameters
    $orderId = (int)$_POST['order_id'];
    $parameters = json_decode($_POST['parameters'], true);
    
    error_log("GENERATE LABELS STANDALONE - Parsed parameters: " . print_r($parameters, true));
    
    // Include necessary files for API client
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Load config
    $config = require __DIR__ . '/../config/config.php';
    
    // Create API client
    $apiClient = new App\Api\OrderApiClient(
        $config['api']['url'],
        $config['api']['key']
    );
    
    // Call the API with the actual form parameters
    $result = $apiClient->generateShippingLabels($orderId, $parameters ?: []);
    
    error_log("GENERATE LABELS STANDALONE - Result: " . print_r($result, true));
    
    // Return JSON response
    header('Content-Type: application/json');
    if ($result !== null) {
        echo json_encode([
            'success' => true,
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

// If not generate_labels request, return error
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>