<?php
/**
 * Installation Setup Script for IAI Zlecanie Listow
 * 
 * This script works when run from the public/ directory
 * and adjusts all paths accordingly.
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>IAI Setup Assistant (Public Directory Version)</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.info { color: blue; }
.section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 3px; }
.path-info { background: #e8f4f8; padding: 10px; border-radius: 3px; margin: 10px 0; }
</style></head><body>";

echo "<h1>IAI Zlecanie Listow - Setup Assistant</h1>";
echo "<p class='info'><strong>Note:</strong> This setup is running from the public/ directory</p>";

// Show current paths
echo "<div class='path-info'>";
echo "<h3>Current Path Information:</h3>";
echo "<p><strong>Script Location:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Parent Directory:</strong> " . dirname(__DIR__) . "</p>";
echo "</div>";

// 1. Check PHP version
echo "<div class='section'>";
echo "<h2>1. PHP Configuration</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION;
if (version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo " <span class='success'>✓ OK</span>";
} else {
    echo " <span class='error'>✗ PHP 7.4+ required</span>";
}
echo "</p>";

// 2. Check required extensions
echo "<h3>Required Extensions:</h3>";
$required_extensions = ['sqlsrv', 'pdo_sqlsrv', 'curl', 'json'];
foreach ($required_extensions as $ext) {
    echo "<p><strong>$ext:</strong> ";
    if (extension_loaded($ext)) {
        echo "<span class='success'>✓ Loaded</span>";
    } else {
        echo "<span class='error'>✗ Not loaded</span>";
    }
    echo "</p>";
}
echo "</div>";

// 3. Check directory structure - adjusted for public/ execution
echo "<div class='section'>";
echo "<h2>2. Directory Structure</h2>";
$current_dir = dirname(__DIR__); // Go up one level from public/
echo "<p><strong>Project root directory:</strong> $current_dir</p>";

$required_dirs = [
    'config' => dirname(__DIR__) . '/config',
    'views' => dirname(__DIR__) . '/views', 
    'storage' => dirname(__DIR__) . '/storage',
    'logs' => dirname(__DIR__) . '/logs',
    'public' => __DIR__ // This should already exist since we're running from it
];

foreach ($required_dirs as $name => $dir_path) {
    echo "<p><strong>$name/:</strong> ";
    if (is_dir($dir_path)) {
        echo "<span class='success'>✓ Exists</span>";
    } else {
        echo "<span class='error'>✗ Missing</span>";
        // Try to create directory
        if (mkdir($dir_path, 0755, true)) {
            echo " <span class='success'>✓ Created</span>";
        } else {
            echo " <span class='error'>✗ Cannot create</span>";
        }
    }
    echo " ($dir_path)</p>";
}
echo "</div>";

// 4. Check config file - adjusted path
echo "<div class='section'>";
echo "<h2>3. Configuration File</h2>";
$config_path = dirname(__DIR__) . '/config/config.php';
echo "<p><strong>Config path:</strong> $config_path</p>";

if (file_exists($config_path)) {
    echo "<p><span class='success'>✓ Configuration file exists</span></p>";
    try {
        $config = require $config_path;
        echo "<p><span class='success'>✓ Configuration file is readable</span></p>";
        
        // Check key config sections
        $required_sections = ['api', 'storage', 'printing', 'mssql'];
        foreach ($required_sections as $section) {
            if (isset($config[$section])) {
                echo "<p><strong>$section section:</strong> <span class='success'>✓ OK</span></p>";
            } else {
                echo "<p><strong>$section section:</strong> <span class='error'>✗ Missing</span></p>";
            }
        }
    } catch (Exception $e) {
        echo "<p><span class='error'>✗ Configuration file has errors: " . $e->getMessage() . "</span></p>";
    }
} else {
    echo "<p><span class='error'>✗ Configuration file not found</span></p>";
    echo "<p class='info'>Creating sample configuration file...</p>";
    
    $sample_config = '<?php

return [
    \'api\' => [
        \'url\' => \'https://dkwadrat.pl/api/admin/v6/orders/orders/search\',
        \'key\' => \'YXBwbGljYXRpb24xODpzSUdaNFU1ZzFwVnV2K3R4bExZU2lxRnR6dytHa0hiY3dhQ29HZ1BOdFdOSEtlekRYR0F3NkpFZEFCZGk0RWQ0\'
    ],
    \'storage\' => [
        \'labels_directory\' => DIRECTORY_SEPARATOR === \'/\' ? \'/tmp/listy_iai\' : \'C:\\\listy_iai\'
    ],
    \'printing\' => [
        \'sumatra_path\' => \'C:\\\Program Files\\\SumatraPDF\\\SumatraPDF.exe\',
        \'default_printer\' => \'Microsoft Print to PDF\'
    ],
    \'mssql\' => [
        \'server\' => \'192.168.230.100,11519\',
        \'database\' => \'D2\',
        \'username\' => \'d2wms\',
        \'password\' => \'Pc$x271\'
    ],
    \'shops\' => [
        4 => \'furnizone.cz\',
        5 => \'dwkadrat.pl\',
        6 => \'b2b.fernity\'
    ]
];';
    
    if (!is_dir(dirname($config_path))) {
        mkdir(dirname($config_path), 0755, true);
    }
    
    if (file_put_contents($config_path, $sample_config)) {
        echo "<p><span class='success'>✓ Sample configuration created</span></p>";
    } else {
        echo "<p><span class='error'>✗ Cannot create configuration file</span></p>";
    }
}
echo "</div>";

// 5. Test database connection
echo "<div class='section'>";
echo "<h2>4. Database Connection Test</h2>";
if (extension_loaded('sqlsrv') && file_exists($config_path)) {
    try {
        $config = require $config_path;
        if (isset($config['mssql'])) {
            $server = $config['mssql']['server'];
            $connection = array(
                "Database" => $config['mssql']['database'],
                "UID" => $config['mssql']['username'],
                "PWD" => $config['mssql']['password']
            );
            
            $conn = sqlsrv_connect($server, $connection);
            if ($conn) {
                echo "<p><span class='success'>✓ Database connection successful</span></p>";
                sqlsrv_close($conn);
            } else {
                echo "<p><span class='error'>✗ Database connection failed</span></p>";
                $errors = sqlsrv_errors();
                if ($errors) {
                    echo "<pre>" . print_r($errors, true) . "</pre>";
                    
                    // Check for specific ODBC driver error
                    foreach ($errors as $error) {
                        if (strpos($error['message'], 'Microsoft ODBC Driver') !== false) {
                            echo "<div class='warning'>";
                            echo "<h4>⚠️ ODBC Driver Missing</h4>";
                            echo "<p>You need to install the Microsoft ODBC Driver for SQL Server:</p>";
                            echo "<ol>";
                            echo "<li>Download from: <a href='https://go.microsoft.com/fwlink/?LinkId=163712' target='_blank'>Microsoft ODBC Driver Download</a></li>";
                            echo "<li>Install the x64 version</li>";
                            echo "<li>Restart Apache</li>";
                            echo "<li>Test again</li>";
                            echo "</ol>";
                            echo "</div>";
                            break;
                        }
                    }
                }
            }
        } else {
            echo "<p><span class='warning'>⚠ MSSQL configuration not found</span></p>";
        }
    } catch (Exception $e) {
        echo "<p><span class='error'>✗ Database test error: " . $e->getMessage() . "</span></p>";
    }
} else {
    echo "<p><span class='warning'>⚠ Cannot test database (sqlsrv not loaded or config missing)</span></p>";
}
echo "</div>";

// 6. Check file permissions and paths
echo "<div class='section'>";
echo "<h2>5. File Path Verification</h2>";
$important_files = [
    'search_orders.php' => __DIR__ . '/search_orders.php',
    'generate_labels.php' => __DIR__ . '/generate_labels.php',
    'print_labels.php' => __DIR__ . '/print_labels.php',
    'order_view.php' => dirname(__DIR__) . '/views/order_view.php'
];

foreach ($important_files as $name => $path) {
    echo "<p><strong>$name:</strong> ";
    if (file_exists($path)) {
        echo "<span class='success'>✓ Found</span>";
    } else {
        echo "<span class='error'>✗ Missing</span>";
    }
    echo " ($path)</p>";
}
echo "</div>";

// 7. Installation instructions
echo "<div class='section'>";
echo "<h2>6. Installation Status & Next Steps</h2>";

if (extension_loaded('sqlsrv') && file_exists($config_path)) {
    echo "<h3>✅ Ready to Test Application</h3>";
    echo "<p>Your setup looks good! You can now:</p>";
    echo "<ul>";
    echo "<li><a href='../views/order_view.php' target='_blank'>Access the main application</a></li>";
    echo "<li><a href='../debug_search.php' target='_blank'>Test search functionality</a></li>";
    echo "<li><a href='search_orders.php' target='_blank'>Test search endpoint</a> (should show 'Invalid request')</li>";
    echo "</ul>";
} else {
    echo "<h3>⚠️ Setup Incomplete</h3>";
    echo "<p>You still need to:</p>";
    echo "<ul>";
    if (!extension_loaded('sqlsrv')) {
        echo "<li>Install Microsoft ODBC Driver for SQL Server</li>";
        echo "<li>Restart Apache after ODBC driver installation</li>";
    }
    if (!file_exists($config_path)) {
        echo "<li>Fix configuration file issues</li>";
    }
    echo "</ul>";
}

echo "<h3>🔧 Troubleshooting Tips:</h3>";
echo "<ul>";
echo "<li><strong>If search doesn't work:</strong> Check browser console (F12) for JavaScript errors</li>";
echo "<li><strong>If database fails:</strong> Verify server IP (192.168.230.100) is accessible</li>";
echo "<li><strong>If printing fails:</strong> Check SumatraPDF installation path</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>