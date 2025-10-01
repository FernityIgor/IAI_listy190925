<?php
/**
 * Installation Setup Script for IAI Zlecanie Listow
 * 
 * This script helps set up the application on different computers
 * by checking configuration and providing setup guidance.
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>IAI Setup Assistant</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { color: green; }
.error { color: red; }
.warning { color: orange; }
.info { color: blue; }
.section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 3px; }
</style></head><body>";

echo "<h1>IAI Zlecanie Listow - Setup Assistant</h1>";

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

// 3. Check directory structure
echo "<div class='section'>";
echo "<h2>2. Directory Structure</h2>";
$current_dir = __DIR__;
echo "<p><strong>Current directory:</strong> $current_dir</p>";

$required_dirs = ['config', 'public', 'views', 'storage', 'logs'];
foreach ($required_dirs as $dir) {
    $dir_path = $current_dir . '/' . $dir;
    echo "<p><strong>$dir/:</strong> ";
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
    echo "</p>";
}
echo "</div>";

// 4. Check config file
echo "<div class='section'>";
echo "<h2>3. Configuration File</h2>";
$config_path = $current_dir . '/config/config.php';
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
        \'labels_directory\' => DIRECTORY_SEPARATOR === \'/\' ? \'/tmp/listy_iai\' : \'C:\listy_iai\'
    ],
    \'printing\' => [
        \'sumatra_path\' => \'C:\Program Files\SumatraPDF\SumatraPDF.exe\',
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

// 6. Installation instructions
echo "<div class='section'>";
echo "<h2>5. Installation Instructions</h2>";
echo "<h3>For XAMPP users:</h3>";
echo "<ol>";
echo "<li>Download Microsoft Drivers for PHP for SQL Server from Microsoft's website</li>";
echo "<li>Copy the appropriate .dll files to your PHP ext directory (e.g., C:\xampp\php\ext\)</li>";
echo "<li>Edit php.ini and add:</li>";
echo "<pre>extension=sqlsrv
extension=pdo_sqlsrv</pre>";
echo "<li>Restart Apache</li>";
echo "<li>Make sure SumatraPDF is installed in C:\Program Files\SumatraPDF\</li>";
echo "<li>Create the labels directory: C:\listy_iai\</li>";
echo "</ol>";

echo "<h3>Troubleshooting:</h3>";
echo "<ul>";
echo "<li>If you get 'Config not found' errors, make sure this script created the config file</li>";
echo "<li>If database connection fails, check your server IP and credentials</li>";
echo "<li>If printing doesn't work, check the SumatraPDF path in config</li>";
echo "</ul>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>6. Next Steps</h2>";
echo "<p>Once all checks above show ✓ OK, you can:</p>";
echo "<ul>";
echo "<li><a href='views/order_view.php'>Access the main application</a></li>";
echo "<li><a href='debug_search.php'>Test search functionality</a></li>";
echo "<li><a href='public/search_orders.php'>Test search endpoint directly</a> (should show 'Invalid request')</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>