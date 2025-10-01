<?php
// Order search endpoint using MSSQL connection

// Set error handling to ensure JSON output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

// Clean any previous output
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Set JSON header early
header('Content-Type: application/json; charset=utf-8');

// Function to send clean JSON response
function sendJsonResponse($data) {
    // Clean all output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    // Send JSON response
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_term'])) {
        $searchTerm = trim($_POST['search_term']);
        
        if (empty($searchTerm)) {
            sendJsonResponse(['success' => false, 'error' => 'Search term cannot be empty']);
        }
        
        // Check if sqlsrv extension is loaded
        if (!extension_loaded('sqlsrv')) {
            sendJsonResponse([
                'success' => false, 
                'error' => 'SQL Server extension (sqlsrv) is not installed or enabled in PHP. Please install Microsoft SQL Server driver for PHP or enable the extension in php.ini',
                'debug_info' => [
                    'php_version' => PHP_VERSION,
                    'loaded_extensions' => array_filter(get_loaded_extensions(), function($ext) {
                        return stripos($ext, 'sql') !== false || stripos($ext, 'odbc') !== false;
                    })
                ]
            ]);
        }
        
        // Load config - try multiple possible locations
        $possibleConfigPaths = [
            __DIR__ . '/../config/config.php',  // Standard location
            __DIR__ . '/config/config.php',     // Alternative location 1
            dirname(__DIR__) . '/config.php',   // Alternative location 2
            __DIR__ . '/../config.php',         // Alternative location 3
        ];
        
        $config = null;
        $configPath = null;
        
        foreach ($possibleConfigPaths as $path) {
            if (file_exists($path)) {
                $configPath = $path;
                $config = require $path;
                break;
            }
        }
        
        if (!$config) {
            sendJsonResponse([
                'success' => false, 
                'error' => 'Configuration file not found in any of the expected locations',
                'debug_info' => [
                    'searched_paths' => $possibleConfigPaths,
                    'current_dir' => __DIR__,
                    'script_location' => __FILE__
                ]
            ]);
        }
        
        // Get MSSQL connection details
        $server = $config['mssql']['server'];
        $connection = array(
            "Database" => $config['mssql']['database'],
            "UID" => $config['mssql']['username'],
            "PWD" => $config['mssql']['password']
        );
        
        // Connect to MSSQL
        $conn = sqlsrv_connect($server, $connection);
        
        if (!$conn) {
            $errors = sqlsrv_errors();
            sendJsonResponse([
                'success' => false, 
                'error' => 'Failed to connect to database', 
                'details' => $errors
            ]);
        }
        
        // Prepare the SQL query
        $sql = "
        SELECT TOP (1000) 
            et.et_Kod, 
            zl.zl_Numer, 
            zl.zl_WFMAG_ID, 
            zam.NR_ZAMOWIENIA_KLIENTA,
            CASE
                WHEN zam.NR_ZAMOWIENIA_KLIENTA NOT LIKE 'BL/%' 
                THEN SUBSTRING(zam.NR_ZAMOWIENIA_KLIENTA, CHARINDEX('/', zam.NR_ZAMOWIENIA_KLIENTA) + 1, LEN(zam.NR_ZAMOWIENIA_KLIENTA))
                ELSE NULL
            END AS iai_zamowienie
        FROM d2wms.dbo.Zlecenie zl
        INNER JOIN d2wms.dbo.Etykieta et ON zl.zl_ID = et.et_Zlecenie
        INNER JOIN d2.dbo.ZAMOWIENIE zam ON zl.zl_WFMAG_ID = zam.ID_ZAMOWIENIA
        WHERE zl.zl_Rodzaj = 1 
            AND (zl.zl_Stan = 3 OR zl.zl_Stan = 7)
            AND zl.zl_DataModyfikacji >= DATEADD(WEEK, -2, GETDATE())
            AND (et.et_Kod LIKE ? OR zl.zl_Numer LIKE ?)
        ORDER BY zl.zl_ID DESC";
        
        // Prepare search parameters (add wildcards for partial matching)
        $searchParam = '%' . $searchTerm . '%';
        $params = array($searchParam, $searchParam);
        
        // Execute query
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt === false) {
            $errors = sqlsrv_errors();
            sendJsonResponse(['success' => false, 'error' => 'Database query failed', 'details' => $errors]);
        }
        
        // Fetch results
        $results = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }
        
        // Close connections
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        
        // Return results
        sendJsonResponse([
            'success' => true,
            'results' => $results,
            'count' => count($results)
        ]);
        
    } else {
        sendJsonResponse(['success' => false, 'error' => 'Invalid request']);
    }
    
} catch (Exception $e) {
    sendJsonResponse([
        'success' => false, 
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    sendJsonResponse([
        'success' => false, 
        'error' => 'Fatal error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>