<?php
// Standalone handler for saving labels to configured directory

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
    
    // Get the configured labels directory
    $labelsDirectory = $config['storage']['labels_directory'] ?? 'C:\listy_iai';
    
    // Ensure directory exists
    if (!is_dir($labelsDirectory)) {
        if (!mkdir($labelsDirectory, 0755, true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Could not create labels directory: ' . $labelsDirectory]);
            exit;
        }
    }
    
    // Create API client
    $apiClient = new App\Api\OrderApiClient(
        $config['api']['url'],
        $config['api']['key'],
        $config
    );
    
    // Download the labels
    $result = $apiClient->downloadLabels($orderNumber);
    
    if ($result && $result['success'] && !empty($result['files'])) {
        $savedFiles = [];
        
        foreach ($result['files'] as $index => $file) {
            $sourceFilePath = $file['filepath'];
            
            if (file_exists($sourceFilePath)) {
                // Generate filename for the destination
                $timestamp = date('Y-m-d_H-i-s');
                $filename = "etykiety-{$orderNumber}-{$timestamp}";
                if (count($result['files']) > 1) {
                    $filename .= "-" . ($index + 1);
                }
                $filename .= ".pdf";
                
                $destinationPath = $labelsDirectory . DIRECTORY_SEPARATOR . $filename;
                
                // Copy file to configured directory
                if (copy($sourceFilePath, $destinationPath)) {
                    $savedFiles[] = [
                        'filename' => $filename,
                        'path' => $destinationPath
                    ];
                    
                    // Optional: Delete temporary file after copying
                    // unlink($sourceFilePath);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Failed to copy file to: ' . $destinationPath]);
                    exit;
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Source PDF file not found: ' . $sourceFilePath]);
                exit;
            }
        }
        
        // Return success with file information
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Labels saved successfully',
            'files' => $savedFiles,
            'directory' => $labelsDirectory
        ]);
        exit;
        
    } else {
        // Return error as JSON
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