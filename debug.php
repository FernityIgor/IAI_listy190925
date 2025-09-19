<?php
// Debug version to see API response structure
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Get the order name from the URL or POST
$orderName = '';
if (isset($_GET['order'])) {
    $orderName = trim($_GET['order']);
} elseif (isset($_POST['order'])) {
    $orderName = trim($_POST['order']);
}

$orderData = null;
$error = '';

// Only make the API call if an order number was submitted
if (!empty($orderName) && (isset($_POST['submit']) || isset($_GET['order']))) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://dkwadrat.pl/api/admin/v6/orders/orders/search",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'params' => [
                'ordersSerialNumbers' => [
                    intval($orderName)
                ]
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "X-API-KEY: YXBwbGljYXRpb24xOktvTnUyTkwrV0NEbUwvMzdhMmJFN3BFSzVTTkVEM2ZjRm9xbzQ5NDREKzd1SXRsNGlPQnFkL0pBb2NMZGZsR3c=",
            "accept: application/json",
            "content-type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        $error = "cURL Error #:" . $err;
    } else {
        $orderData = json_decode($response, true);
        
        // Log the raw response for debugging
        file_put_contents('/var/www/html/storage/logs/api_response.json', $response);
        file_put_contents('/var/www/html/storage/logs/api_parsed.json', json_encode($orderData, JSON_PRETTY_PRINT));
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Debug Order Details</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
        pre { background: #fff; padding: 10px; border: 1px solid #ccc; overflow: auto; max-height: 400px; }
    </style>
</head>
<body>
    <h1>Debug Order API Response</h1>
    
    <form method="post">
        <label>Order ID:</label>
        <input type="text" name="order" value="<?= h($orderName) ?>">
        <input type="submit" name="submit" value="Submit">
    </form>
    
    <?php if ($error): ?>
        <div class="debug">
            <h3>Error:</h3>
            <p><?= h($error) ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($orderData): ?>
        <div class="debug">
            <h3>Raw API Response Structure:</h3>
            <pre><?= h(json_encode($orderData, JSON_PRETTY_PRINT)) ?></pre>
        </div>
        
        <div class="debug">
            <h3>Available Keys in Response:</h3>
            <ul>
                <?php foreach (array_keys($orderData) as $key): ?>
                    <li><?= h($key) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <?php if (isset($orderData['Results']) && is_array($orderData['Results'])): ?>
            <div class="debug">
                <h3>Results Array Count:</h3>
                <p><?= count($orderData['Results']) ?> results found</p>
                
                <?php if (!empty($orderData['Results'])): ?>
                    <h3>First Result Keys:</h3>
                    <ul>
                        <?php foreach (array_keys($orderData['Results'][0]) as $key): ?>
                            <li><?= h($key) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>