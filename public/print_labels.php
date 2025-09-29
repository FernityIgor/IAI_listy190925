<?php
// Standalone handler for printing labels using SumatraPDF

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
        
        // Find the most recent PDF file for this order
        $pattern = $labelsDirectory . DIRECTORY_SEPARATOR . "etykiety-{$orderNumber}-*.pdf";
        $files = glob($pattern);
        
        if (empty($files)) {
            echo json_encode(['success' => false, 'error' => 'No PDF file found for order ' . $orderNumber]);
            exit;
        }
        
        // Sort files by modification time (most recent first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $pdfFile = $files[0]; // Get the most recent file
        
        if (!is_file($pdfFile)) {
            echo json_encode(['success' => false, 'error' => 'PDF file not found: ' . $pdfFile]);
            exit;
        }
        
        // SumatraPDF configuration from config
        $sumatra = $config['printing']['sumatra_path'] ?? 'C:\Program Files\SumatraPDF\SumatraPDF.exe';
        $printer = $config['printing']['default_printer'] ?? 'Microsoft Print to PDF';
        
        if (!is_file($sumatra)) {
            echo json_encode(['success' => false, 'error' => 'SumatraPDF not found at: ' . $sumatra]);
            exit;
        }
        
        // Build the command
        // -print-to "DRUKARKA" -silent -exit-on-print "plik.pdf"
        $cmd = '"' . $sumatra . '" -print-to "' . $printer . '" -silent -exit-on-print ' . escapeshellarg($pdfFile);
        
        // Execute the command
        $output = [];
        $returnCode = 0;
        exec($cmd . ' 2>&1', $output, $returnCode);
        
        // Log the command and output for debugging
        error_log("Print command: $cmd");
        error_log("Print output: " . implode("\n", $output));
        error_log("Print return code: $returnCode");
        
        if ($returnCode === 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Labels sent to printer successfully',
                'file' => basename($pdfFile),
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
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
    }
    
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