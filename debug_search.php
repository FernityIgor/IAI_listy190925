<?php
// Test script to debug search_orders.php issues
// Save this as debug_search.php and access it directly in browser

echo "<h2>Debug Information for search_orders.php</h2>";

echo "<h3>1. PHP Version and Extensions</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "SQLSRV Extension Loaded: " . (extension_loaded('sqlsrv') ? 'YES' : 'NO') . "<br>";

echo "<h3>2. SQL-related Extensions:</h3>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (stripos($ext, 'sql') !== false || stripos($ext, 'odbc') !== false) {
        echo $ext . "<br>";
    }
}

echo "<h3>3. Config File Check</h3>";
$configPath = __DIR__ . '/config/config.php';
echo "Config Path: " . $configPath . "<br>";
echo "Config Exists: " . (file_exists($configPath) ? 'YES' : 'NO') . "<br>";

if (file_exists($configPath)) {
    echo "<h3>4. Config Contents (partial)</h3>";
    try {
        $config = require $configPath;
        echo "MSSQL Server: " . (isset($config['mssql']['server']) ? $config['mssql']['server'] : 'NOT SET') . "<br>";
        echo "MSSQL Database: " . (isset($config['mssql']['database']) ? $config['mssql']['database'] : 'NOT SET') . "<br>";
        echo "MSSQL Username: " . (isset($config['mssql']['username']) ? $config['mssql']['username'] : 'NOT SET') . "<br>";
    } catch (Exception $e) {
        echo "Error loading config: " . $e->getMessage() . "<br>";
    }
}

echo "<h3>5. Test Search Request</h3>";
echo "<form method='POST' action='public/search_orders.php'>";
echo "Search Term: <input type='text' name='search_term' value='test'>";
echo "<input type='submit' value='Test Search'>";
echo "</form>";

echo "<h3>6. Direct Access Test</h3>";
echo "<a href='public/search_orders.php' target='_blank'>Click here to test direct access to search_orders.php</a><br>";
echo "(This should show an error about invalid request method)";

echo "<h3>7. Error Logs</h3>";
echo "Error Log Location: " . ini_get('error_log') . "<br>";
echo "Display Errors: " . ini_get('display_errors') . "<br>";
echo "Log Errors: " . ini_get('log_errors') . "<br>";

?>