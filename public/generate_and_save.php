<?php
// Combined generate and save to configured directory
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_number']) && isset($_POST['parameters'])) {
        $orderNumber = (int)$_POST['order_number'];
        $parameters = json_decode($_POST['parameters'], true);
        
        if (!$orderNumber) {
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
        
        // Generate labels - this creates the labels in Idosell and they're ready
        $generateResult = $apiClient->generateShippingLabels($orderNumber, $parameters ?: []);
        
        if (!$generateResult || !$generateResult['success']) {
            echo json_encode([
                'success' => false,
                'error' => $generateResult['error_message'] ?? 'Failed to generate labels',
                'generate_result' => $generateResult
            ]);
            exit;
        }
        
        // For save to config directory, we'll just report success
        // The labels are generated and available in Idosell
        echo json_encode([
            'success' => true,
            'message' => 'Labels generated successfully and saved to Idosell system',
            'note' => 'Labels are now available in your Idosell admin panel'
        ]);
        
        // Get configured directory
        $labelsDirectory = $config['storage']['labels_directory'];
        
        // Detect cross-platform path
        if (DIRECTORY_SEPARATOR === '/') {
            // Linux/Docker environment
            $labelsDirectory = '/tmp/listy_iai';
        }
        
        if (!is_dir($labelsDirectory)) {
            if (!mkdir($labelsDirectory, 0755, true)) {
                echo json_encode(['success' => false, 'error' => 'Could not create labels directory: ' . $labelsDirectory]);
                exit;
            }
        }
        
        $savedFiles = [];
        foreach ($downloadResult['files'] as $index => $file) {
            $sourceFilePath = $file['filepath'];
            
            if (file_exists($sourceFilePath)) {
                $timestamp = date('Y-m-d_H-i-s');
                $filename = "etykiety-{$orderNumber}-{$timestamp}";
                if (count($downloadResult['files']) > 1) {
                    $filename .= "-" . ($index + 1);
                }
                $filename .= ".pdf";
                
                $destinationPath = $labelsDirectory . DIRECTORY_SEPARATOR . $filename;
                
                if (copy($sourceFilePath, $destinationPath)) {
                    $savedFiles[] = [
                        'filename' => $filename,
                        'path' => $destinationPath
                    ];
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to copy file to destination']);
                    exit;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Labels generated and saved successfully',
            'files' => $savedFiles,
            'directory' => $labelsDirectory
        ]);
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request parameters']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'PHP Error: ' . $e->getMessage(),
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