<?php
echo "<h1>PHP Environment Info</h1>";
echo "<h2>PHP Version: " . phpversion() . "</h2>";

echo "<h3>Loaded Extensions:</h3>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $ext) {
    echo $ext . "<br>";
}

echo "<h3>cURL Info:</h3>";
if (function_exists('curl_version')) {
    $curl_info = curl_version();
    echo "cURL Version: " . $curl_info['version'] . "<br>";
    echo "SSL Version: " . $curl_info['ssl_version'] . "<br>";
    echo "Protocols: " . implode(', ', $curl_info['protocols']) . "<br>";
} else {
    echo "cURL not available!<br>";
}

echo "<h3>File Permissions Test:</h3>";
$testFile = '/var/www/html/storage/logs/test_write.txt';
if (is_writable(dirname($testFile))) {
    echo "Storage directory is writable<br>";
    if (file_put_contents($testFile, 'test')) {
        echo "File write test: SUCCESS<br>";
        unlink($testFile);
    } else {
        echo "File write test: FAILED<br>";
    }
} else {
    echo "Storage directory is NOT writable<br>";
}

echo "<h3>Current Working Directory:</h3>";
echo getcwd() . "<br>";

echo "<h3>Config File Check:</h3>";
$configPath = '/var/www/html/config/config.php';
if (file_exists($configPath)) {
    echo "Config file exists<br>";
    $config = include $configPath;
    echo "API URL: " . $config['api']['url'] . "<br>";
    echo "Labels directory: " . $config['storage']['labels_directory'] . "<br>";
} else {
    echo "Config file NOT found<br>";
}

echo "<h3>Simple cURL Test:</h3>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/get');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "cURL Error: " . $error . "<br>";
} else {
    echo "cURL Test: HTTP " . $httpCode . " - " . (strlen($result) > 0 ? "SUCCESS" : "NO DATA") . "<br>";
}
?>