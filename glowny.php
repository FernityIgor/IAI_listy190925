<?php
// Load configuration
$config = include 'config/config.php';

// Helper function to escape HTML
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
        CURLOPT_URL => $config['api']['url'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'params' => [
                'ordersSerialNumbers' => [
                    intval($orderName)  // Convert order number to integer
                ]
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "X-API-KEY: " . $config['api']['key'],
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
    }
}

// Process the API response data correctly
if ($orderData && isset($orderData['Results'][0])) {
    $order = $orderData['Results'][0];
    $clientResult = $order['clientResult'];
    // Fix: products are in orderDetails -> productsResults (not just productsResults)
    $products = $order['orderDetails']['productsResults'] ?? [];
} else {
    $order = null;
    $clientResult = null;
    $products = [];
}

// Debug information for troubleshooting
$debugInfo = '';
if ($orderData) {
    $debugInfo = "Debug Info: " . count($orderData['Results'] ?? []) . " results found. ";
    if (isset($orderData['Results'][0])) {
        $productsCount = count($orderData['Results'][0]['orderDetails']['productsResults'] ?? []);
        $debugInfo .= "Products found: " . $productsCount . ". ";
    }
}

// Common HTML header and search form
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
    <style>
        body {
            background: #f3f4f6;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .search-container {
            position: static; /* Remove fixed positioning */
            width: auto; /* Remove fixed width */
            height: auto; /* Remove fixed height */
            background: #fff;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            flex: 1; /* Make it flex like other boxes */
        }
        .search-container form {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .search-container label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }
        .search-container input[type="text"] {
            width: calc(100% - 16px);
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
        }
        .search-container input[type="submit"] {
            width: 100%;
            margin-top: 10px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-container input[type="submit"]:hover {
            background: #1d4ed8;
        }

        .address-boxes {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            gap: 10px;
        }

        .address-box {
            flex: 1;
            background: #fff;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .address-box h3 {
            margin-top: 0;
            margin-bottom: 8px;
            color: #2563eb;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .address-box p {
            margin: 5px 0;
            line-height: 1.3;
        }

        .products-table {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .products-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th,
        .products-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .products-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
        }

        .products-table tr:hover {
            background: #f1f5f9;
        }

        .pickup-point {
            color: #2563eb;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .container {
            max-width: 100%; /* Use full width */
            margin: 20px;
        }

        .order-details-box {
            background: #fff;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }

        .order-details-box h3 {
            margin-top: 0;
            margin-bottom: 8px;
            color: #2563eb;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .order-details-box p {
            margin: 5px 0;
            line-height: 1.3;
        }

        @media (max-width: 1200px) {
            .address-boxes {
                flex-wrap: wrap;
            }
            .address-box, .search-container {
                flex: 1 1 calc(50% - 5px);
                margin-bottom: 10px;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
            .address-box, .search-container {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="address-boxes">
            <div class="search-container address-box">
                <h3>Search Order</h3>
                <form method="post">
                    <label for="order">Enter Order ID:</label>
                    <input type="text" id="order" name="order" value="<?= h($orderName) ?>">
                    <input type="submit" name="submit" value="Submit">
                </form>
                <?php if ($error): ?>
                    <p style="color: red;"><?= h($error) ?></p>
                <?php endif; ?>
                <?php if ($debugInfo): ?>
                    <p style="color: blue; font-size: 12px;"><?= h($debugInfo) ?></p>
                <?php endif; ?>
            </div>

            <div class="address-box">
                <h3>Client Account</h3>
                <?php if (isset($clientResult['endClientAccount'])): ?>
                    <p><strong>Login:</strong> <?= h($clientResult['endClientAccount']['clientLogin']) ?></p>
                    <p><strong>Email:</strong> <?= h($clientResult['endClientAccount']['clientEmail']) ?></p>
                    <p><strong>Phone:</strong> <?= h($clientResult['endClientAccount']['clientPhone1']) ?></p>
                <?php endif; ?>
            </div>

            <div class="address-box">
                <h3>Billing Address</h3>
                <?php if (isset($clientResult['clientBillingAddress'])): ?>
                    <p><strong>Name:</strong> <?= h($clientResult['clientBillingAddress']['clientFirstName']) ?> 
                        <?= h($clientResult['clientBillingAddress']['clientLastName']) ?></p>
                    <p><strong>Street:</strong> <?= h($clientResult['clientBillingAddress']['clientStreet']) ?></p>
                    <p><strong>City:</strong> <?= h($clientResult['clientBillingAddress']['clientCity']) ?> 
                        <?= h($clientResult['clientBillingAddress']['clientZipCode']) ?></p>
                    <p><strong>Phone:</strong> <?= h($clientResult['endClientAccount']['clientPhone1']) ?></p>
                <?php else: ?>
                    <p>No billing address data available</p>
                    <?php if ($clientResult): ?>
                        <p style="font-size: 11px;">Available keys: <?= h(implode(', ', array_keys($clientResult))) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="address-box">
                <h3>Delivery Location</h3>
                <?php if (isset($clientResult['clientPickupPointAddress'])): ?>
                    <p class="pickup-point"><strong>Pickup Point:</strong></p>
                    <p><strong>Name:</strong> <?= h($clientResult['clientPickupPointAddress']['name']) ?></p>
                    <p><strong>Address:</strong> <?= h($clientResult['clientPickupPointAddress']['street']) ?></p>
                    <p><strong>City:</strong> <?= h($clientResult['clientPickupPointAddress']['city']) ?> 
                        <?= h($clientResult['clientPickupPointAddress']['zipCode']) ?></p>
                    <p><strong>Phone:</strong> <?= h($clientResult['endClientAccount']['clientPhone1']) ?></p>
                <?php elseif (isset($clientResult['clientDeliveryAddress'])): ?>
                    <p><strong>Name:</strong> <?= h($clientResult['clientDeliveryAddress']['clientDeliveryAddressFirstName']) ?> 
                        <?= h($clientResult['clientDeliveryAddress']['clientDeliveryAddressLastName']) ?></p>
                    <p><strong>Street:</strong> <?= h($clientResult['clientDeliveryAddress']['clientDeliveryAddressStreet']) ?></p>
                    <p><strong>City:</strong> <?= h($clientResult['clientDeliveryAddress']['clientDeliveryAddressCity']) ?> 
                        <?= h($clientResult['clientDeliveryAddress']['clientDeliveryAddressZipCode']) ?></p>
                    <p><strong>Phone:</strong> <?= h($clientResult['endClientAccount']['clientPhone1']) ?></p>
                <?php else: ?>
                    <p>No delivery address data available</p>
                    <?php if ($clientResult): ?>
                        <p style="font-size: 11px;">Looking for: clientPickupPointAddress or clientDeliveryAddress</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="order-details-box">
            <h3>Order Details</h3>
            <?php if ($order): ?>
            <?php 
            $shopId = $order['orderDetails']['orderSourceResults']['shopId'] ?? null;
            $shopName = $config['shops'][$shopId] ?? 'Unknown Shop';
            ?>
            <p><strong>Shop:</strong> <?= h($shopName) ?> (ID: <?= h($shopId) ?>)</p>
            <p><strong>Order Number:</strong> <?= h($order['orderSerialNumber']) ?></p>
            <p><strong>Order Date:</strong> <?= h($order['orderDetails']['orderAddDate']) ?></p>
            <p><strong>Payment Type:</strong> <?= h($order['orderDetails']['payments']['orderPaymentType']) ?></p>
            <p><strong>Estimated Delivery:</strong> <?= h($order['orderDetails']['dispatch']['estimatedDeliveryDate']) ?></p>
            <?php endif; ?>
        </div>

        <div class="products-table">
            <h3>Products</h3>
            <?php if (!empty($products)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= h($product['productId']) ?></td>
                        <td><?= h($product['productName']) ?></td>
                        <td><?= h($product['productCode']) ?></td>
                        <td><?= h($product['productQuantity']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php 
// ...end of file