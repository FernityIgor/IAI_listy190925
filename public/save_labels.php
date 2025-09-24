<?php
// Standalone handler for saving labels to configured directory

// Set error handling to ensure JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header early
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_number'])) {
        // Parse parameters
        $orderNumber = (int)$_POST['order_number'];
        
        if (!$orderNumber) {
            echo json_encode(['success' => false, 'error' => 'Invalid order number']);
            exit;
        }
        
        // Include necessary files for API client
        require_once __DIR__ . '/../vendor/autoload.php';
        
        // Load config
        $config = require __DIR__ . '/../config/config.php';
        
        // Get the configured labels directory and normalize for current OS
        $configuredDirectory = $config['storage']['labels_directory'] ?? 'C:\listy_iai';
        
        // Convert Windows path to Linux path if needed
        if (DIRECTORY_SEPARATOR === '/' && strpos($configuredDirectory, 'C:') === 0) {
            // Running on Linux/Unix but config has Windows path
            $labelsDirectory = '/tmp/listy_iai'; // Use /tmp as fallback for Docker
        } else {
            $labelsDirectory = $configuredDirectory;
        }
        
        // Ensure directory exists
        if (!is_dir($labelsDirectory)) {
            if (!mkdir($labelsDirectory, 0755, true)) {
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
                // Now we work with binary data instead of file paths
                $binaryData = $file['binary_data'];
                
                if ($binaryData) {
                    // Generate filename for the destination
                    $timestamp = date('Y-m-d_H-i-s');
                    $filename = "etykiety-{$orderNumber}-{$timestamp}";
                    if (count($result['files']) > 1) {
                        $filename .= "-" . ($index + 1);
                    }
                    $filename .= ".pdf";
                    
                    $destinationPath = $labelsDirectory . DIRECTORY_SEPARATOR . $filename;
                    
                    // Save binary data to file
                    if (file_put_contents($destinationPath, $binaryData) !== false) {
                        $savedFiles[] = [
                            'filename' => $filename,
                            'path' => $destinationPath
                        ];
                        
                        // Optional: Delete temporary file after copying
                        // unlink($sourceFilePath);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to copy file to: ' . $destinationPath]);
                        exit;
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Source PDF file not found: ' . $sourceFilePath]);
                    exit;
                }
            }
            
            // Return success with file information
            echo json_encode([
                'success' => true, 
                'message' => 'Labels saved successfully',
                'files' => $savedFiles,
                'directory' => $labelsDirectory
            ]);
            exit;
            
        } else {
            // Return error as JSON
            echo json_encode([
                'success' => false,
                'error' => $result['error'] ?? 'Failed to download labels',
                'api_response' => $result['api_response'] ?? null
            ]);
            exit;
        }
    }
    
    // If not proper request, return error
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    
} catch (Exception $e) {
    // Catch any exceptions and return JSON error
    echo json_encode([
        'success' => false, 
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    // Catch fatal errors and return JSON error
    echo json_encode([
        'success' => false, 
        'error' => 'Fatal error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>