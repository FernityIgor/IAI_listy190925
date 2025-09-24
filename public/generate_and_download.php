<?php
// Combined generate and download to browser
error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_number']) && isset($_POST['parameters'])) {
    try {
        $orderNumber = (int)$_POST['order_number'];
        $parameters = json_decode($_POST['parameters'], true);
        
        if (!$orderNumber) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid order number']);
            exit;
        }
        
        // Include necessary files
        require_once __DIR__ . '/../vendor/autoload.php';
        $config = require __DIR__ . '/../config/config.php';
        
        // Create API client
        $apiClient = new App\Api\OrderApiClient(
            $config['api']['url'],
            $config['api']['key'],
            $config
        );
        
        // Step 1: Generate labels
        $generateResult = $apiClient->generateShippingLabels($orderNumber, $parameters ?: []);
        
        if (!$generateResult || !$generateResult['success']) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $generateResult['error_message'] ?? 'Failed to generate labels'
            ]);
            exit;
        }
        
        // Step 2: Wait a moment then download labels
        sleep(3); // Give the API time to process
        $downloadResult = $apiClient->downloadLabels($orderNumber);
        
        if (!$downloadResult || !$downloadResult['success'] || empty($downloadResult['files'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Failed to download generated labels'
            ]);
            exit;
        }
        
        // Get the first PDF file and send it to browser
        $firstFile = $downloadResult['files'][0];
        $filePath = $firstFile['filepath'];
        
        if (file_exists($filePath)) {
            // Set headers for direct PDF download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="etykiety-' . $orderNumber . '.pdf"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            
            // Send file directly to browser
            readfile($filePath);
            
            // Optional: Clean up temp file after download
            // unlink($filePath);
            
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Generated PDF file not found']);
            exit;
        }
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'PHP Error: ' . $e->getMessage()
        ]);
        exit;
    } catch (Error $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Fatal Error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// If not proper request, return error
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>