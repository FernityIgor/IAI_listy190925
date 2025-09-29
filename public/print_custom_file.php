<?php
// Standalone handler for printing custom PDF files using SumatraPDF

// Set error handling to ensure JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header early
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
        $uploadedFile = $_FILES['pdf_file'];
        
        // Validate file upload
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'File upload error: ' . $uploadedFile['error']]);
            exit;
        }
        
        // Validate file type
        if ($uploadedFile['type'] !== 'application/pdf') {
            echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed']);
            exit;
        }
        
        // Validate file extension
        $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'pdf') {
            echo json_encode(['success' => false, 'error' => 'Only PDF files are allowed']);
            exit;
        }
        
        // Load config
        $config = require __DIR__ . '/../config/config.php';
        
        // Get the configured labels directory for temporary storage
        $configuredDirectory = $config['storage']['labels_directory'] ?? 'C:\listy_iai';
        
        // Convert Windows path to Linux path if needed
        if (DIRECTORY_SEPARATOR === '/' && strpos($configuredDirectory, 'C:') === 0) {
            // Running on Linux/Unix but config has Windows path
            $tempDirectory = '/tmp/listy_iai';
        } else {
            $tempDirectory = $configuredDirectory;
        }
        
        // Ensure temp directory exists
        if (!is_dir($tempDirectory)) {
            if (!mkdir($tempDirectory, 0755, true)) {
                echo json_encode(['success' => false, 'error' => 'Could not create temp directory: ' . $tempDirectory]);
                exit;
            }
        }
        
        // Create temporary file path
        $timestamp = date('Y-m-d_H-i-s');
        $tempFileName = 'custom_print_' . $timestamp . '_' . basename($uploadedFile['name']);
        $tempFilePath = $tempDirectory . DIRECTORY_SEPARATOR . $tempFileName;
        
        // Move uploaded file to temp directory
        if (!move_uploaded_file($uploadedFile['tmp_name'], $tempFilePath)) {
            echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
            exit;
        }
        
        // SumatraPDF configuration from config
        $sumatra = $config['printing']['sumatra_path'] ?? 'C:\Program Files\SumatraPDF\SumatraPDF.exe';
        $printer = $config['printing']['default_printer'] ?? 'Microsoft Print to PDF';
        
        if (!is_file($sumatra)) {
            // Clean up temp file
            unlink($tempFilePath);
            echo json_encode(['success' => false, 'error' => 'SumatraPDF not found at: ' . $sumatra]);
            exit;
        }
        
        // Build the command
        $cmd = '"' . $sumatra . '" -print-to "' . $printer . '" -silent -exit-on-print ' . escapeshellarg($tempFilePath);
        
        // Execute the command
        $output = [];
        $returnCode = 0;
        exec($cmd . ' 2>&1', $output, $returnCode);
        
        // Log the command and output for debugging
        error_log("Custom print command: $cmd");
        error_log("Custom print output: " . implode("\n", $output));
        error_log("Custom print return code: $returnCode");
        
        // Clean up temporary file
        unlink($tempFilePath);
        
        if ($returnCode === 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Custom PDF sent to printer successfully',
                'file' => $uploadedFile['name'],
                'printer' => $printer,
                'command' => $cmd
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Printing failed with return code: ' . $returnCode,
                'output' => implode("\n", $output),
                'command' => $cmd
            ]);
        }
        
    } else {
        // If not proper request, return error
        echo json_encode(['success' => false, 'error' => 'Invalid request - no PDF file uploaded']);
    }
    
} catch (Exception $e) {
    // Clean up temp file if it exists
    if (isset($tempFilePath) && file_exists($tempFilePath)) {
        unlink($tempFilePath);
    }
    
    // Catch any exceptions and return JSON error
    echo json_encode([
        'success' => false, 
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    // Clean up temp file if it exists
    if (isset($tempFilePath) && file_exists($tempFilePath)) {
        unlink($tempFilePath);
    }
    
    // Catch fatal errors and return JSON error
    echo json_encode([
        'success' => false, 
        'error' => 'Fatal error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>