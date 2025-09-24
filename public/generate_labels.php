<?php
// Standalone handler for generate_labels
error_reporting(0); // Suppress error output that breaks JSON
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_labels') {
    try {
        // Parse parameters
        $orderId = (int)$_POST['order_id'];
        $parameters = json_decode($_POST['parameters'], true);
        
        // Debug logging - zapisz do pliku
        $debugLog = "=== GENERATE LABELS DEBUG ===\n";
        $debugLog .= "Order ID: " . $orderId . "\n";
        $debugLog .= "Raw parameters JSON: " . $_POST['parameters'] . "\n";
        $debugLog .= "Decoded parameters: " . print_r($parameters, true) . "\n";
        $debugLog .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Create logs directory if it doesn't exist
        $logDir = __DIR__ . '/../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logDir . '/debug_labels.log', $debugLog, FILE_APPEND);
        
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
    
    // Call the API with the form parameters
    $result = $apiClient->generateShippingLabels($orderId, $parameters ?: []);
    
    // Return JSON response
    header('Content-Type: application/json');
    if ($result !== null) {
        // Add the result to debug log
        $debugLog = "=== GENERATE RESULT DEBUG ===\n";
        $debugLog .= "Result: " . print_r($result, true) . "\n";
        $debugLog .= "Success check: " . (isset($result['success']) ? ($result['success'] ? 'true' : 'false') : 'not set') . "\n";
        $debugLog .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
        file_put_contents($logDir . '/debug_labels.log', $debugLog, FILE_APPEND);
        
        // Check if result contains success information
        if (isset($result['success']) && $result['success'] === true) {
            echo json_encode([
                'success' => true,
                'result' => $result
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $result['error_message'] ?? 'Nieznany błąd API',
                'error_code' => $result['error_code'] ?? 'unknown',
                'http_status' => $result['http_status'] ?? 'unknown',
                'api_response' => $result['api_response'] ?? null
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'API call failed - no response'
        ]);
    }
    exit;
    
    } catch (Exception $e) {
        // Catch any PHP errors and return as JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'PHP Error: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        exit;
    }
}

// If not generate_labels request, return error
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>