<?php
// Standalone handler for generate_labels

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_labels') {
    // Parse parameters
    $orderId = (int)$_POST['order_id'];
    $parameters = json_decode($_POST['parameters'], true);
    
    // Debug logging - zapisz do pliku
    $debugLog = "=== GENERATE LABELS DEBUG ===\n";
    $debugLog .= "Order ID: " . $orderId . "\n";
    $debugLog .= "Raw parameters JSON: " . $_POST['parameters'] . "\n";
    $debugLog .= "Decoded parameters: " . print_r($parameters, true) . "\n";
    $debugLog .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
    file_put_contents(__DIR__ . '/../logs/debug_labels.log', $debugLog, FILE_APPEND);
    
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
        // Check if result contains error information
        if (isset($result['success']) && !$result['success']) {
            echo json_encode([
                'success' => false,
                'error' => $result['error_message'] ?? 'Nieznany błąd API',
                'error_code' => $result['error_code'] ?? 'unknown',
                'http_status' => $result['http_status'] ?? 'unknown',
                'api_response' => $result['api_response'] ?? null
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'result' => $result
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'API call failed - no response'
        ]);
    }
    exit;
}

// If not generate_labels request, return error
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>