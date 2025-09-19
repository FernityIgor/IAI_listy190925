<?php
// Test script for downloading labels
// Usage: php test_download_labels.php [order_number]

require_once __DIR__ . '/vendor/autoload.php';

// Get order number from command line or use default
$orderNumber = $argv[1] ?? 97555;

echo "Testing label download for order: $orderNumber\n";

// Load config
$config = require __DIR__ . '/config/config.php';

// Create API client
$apiClient = new App\Api\OrderApiClient(
    $config['api']['url'],
    $config['api']['key'],
    $config
);

// Download labels
echo "Calling downloadLabels API...\n";
$result = $apiClient->downloadLabels((int)$orderNumber);

if ($result === null) {
    echo "ERROR: API call returned null\n";
    exit(1);
}

echo "API Result:\n";
print_r($result);

if ($result['success'] ?? false) {
    echo "\nSUCCESS! Downloaded {$result['total_labels']} label(s)\n";
    
    if (!empty($result['files'])) {
        echo "Files saved:\n";
        foreach ($result['files'] as $file) {
            echo "  - {$file['filename']} ({$file['size']} bytes)\n";
            echo "    Path: {$file['filepath']}\n";
        }
    }
} else {
    echo "\nFAILED to download labels\n";
    if (isset($result['error'])) {
        echo "Error: {$result['error']}\n";
    }
    if (isset($result['raw_response'])) {
        echo "Raw API response: {$result['raw_response']}\n";
    }
}
?>