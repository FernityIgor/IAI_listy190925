<?php
// Standalone handler for generate_labels

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_labels') {
    // Parse parameters
    $orderId = (int)$_POST['order_id'];
    $parameters = json_decode($_POST['parameters'], true);
    
    // Include necessary files for API client
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Load config
    $config = require __DIR__ . '/../config/config.php';
    
    // Create API client
    $apiClient = new App\Api\OrderApiClient(
        $config['api']['url'],
        $config['api']['key']
    );
    
    // Call the API with the form parameters
    $result = $apiClient->generateShippingLabels($orderId, $parameters ?: []);
    
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