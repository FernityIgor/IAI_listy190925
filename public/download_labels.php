<?php
// Standalone handler for download_labels

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_number'])) {
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
    
    if ($result && $result['success'] && !empty($result['files'])) {
        // Get the first PDF binary data
        $firstFile = $result['files'][0];
        $binaryData = $firstFile['binary_data'];
        
        if ($binaryData) {
            // Set headers for direct PDF download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="etykiety-' . $orderNumber . '.pdf"');
            header('Content-Length: ' . strlen($binaryData));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            
            // Send binary data directly to browser
            echo $binaryData;
            
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'PDF file not found on server']);
            exit;
        }
    } else {
        // Return error as JSON for AJAX handling
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to download labels',
            'api_response' => $result['api_response'] ?? null
        ]);
        exit;
    }
}

// If not proper request, return error
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>