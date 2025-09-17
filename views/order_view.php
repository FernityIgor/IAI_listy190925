<div class="container">
    <div class="address-boxes">
        <div class="search-container address-box">
            <h3>Search Order</h3>
            <form method="post">
                <label for="order">Enter Order ID:</label>
                <input type="text" id="order" name="order" value="<?= $h($orderName) ?>">
                <input type="submit" name="submit" value="Submit">
            </form>
            <?php if ($error): ?>
                <p style="color: red;"><?= $h($error) ?></p>
            <?php endif; ?>
        </div>

        <div class="address-box">
            <h3>Client Account</h3>
            <?php if ($clientResult && isset($clientResult['endClientAccount'])): ?>
                <p><strong>Login:</strong> <?= $h($clientResult['endClientAccount']['clientLogin']) ?></p>
                <p><strong>Email:</strong> <?= $h($clientResult['endClientAccount']['clientEmail']) ?></p>
                <p><strong>Phone:</strong> <?= $h($clientResult['endClientAccount']['clientPhone1']) ?></p>
            <?php endif; ?>
        </div>

        <div class="address-box">
            <h3>Billing Address</h3>
            <?php if ($clientResult && isset($clientResult['clientBillingAddress'])): ?>
                <p><strong>Name:</strong> <?= $h($clientResult['clientBillingAddress']['clientFirstName']) ?> 
                    <?= $h($clientResult['clientBillingAddress']['clientLastName']) ?></p>
                <p><strong>Street:</strong> <?= $h($clientResult['clientBillingAddress']['clientStreet']) ?></p>
                <p><strong>City:</strong> <?= $h($clientResult['clientBillingAddress']['clientCity']) ?> 
                    <?= $h($clientResult['clientBillingAddress']['clientZipCode']) ?></p>
                <p><strong>Phone:</strong> <?= $h($clientResult['endClientAccount']['clientPhone1']) ?></p>
            <?php endif; ?>
        </div>

        <div class="address-box">
            <h3>Delivery Location</h3>
            <?php if ($clientResult && isset($clientResult['clientDeliveryAddress'])): ?>
                <p><strong>Name:</strong> <?= $h($clientResult['clientDeliveryAddress']['clientDeliveryAddressFirstName']) ?> 
                    <?= $h($clientResult['clientDeliveryAddress']['clientDeliveryAddressLastName']) ?></p>
                <p><strong>Street:</strong> <?= $h($clientResult['clientDeliveryAddress']['clientDeliveryAddressStreet']) ?></p>
                <p><strong>City:</strong> <?= $h($clientResult['clientDeliveryAddress']['clientDeliveryAddressCity']) ?> 
                    <?= $h($clientResult['clientDeliveryAddress']['clientDeliveryAddressZipCode']) ?></p>
                <p><strong>Phone:</strong> <?= $h($clientResult['endClientAccount']['clientPhone1']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($order): // Only show these boxes if an order was found ?>
    <div class="order-details-box">
        <h3>Order Details</h3>
        <?php 
        $shopId = $order['orderDetails']['orderSourceResults']['shopId'] ?? null;
        $shopName = $shopNames[$shopId] ?? 'Unknown Shop';
        ?>
        <p><strong>Shop:</strong> <?= $h($shopName) ?> (ID: <?= $h($shopId) ?>)</p>
        <p><strong>Order Number:</strong> <?= $h($order['orderSerialNumber']) ?></p>
        <p><strong>Order Date:</strong> <?= $h($order['orderDetails']['orderAddDate']) ?></p>
        <p><strong>Payment Type:</strong> <?= $h($order['orderDetails']['payments']['orderPaymentType']) ?></p>
        <p><strong>Estimated Delivery:</strong> <?= $h($order['orderDetails']['dispatch']['estimatedDeliveryDate']) ?></p>
    </div>

    <?php if (!empty($packages)): ?>
    <div class="packages-box">
        <h3>Packages</h3>
        <p class="courier-info">
            <?php if (isset($changeableCouriers[$courierId])): ?>
                <button 
                    type="button" 
                    class="change-courier-btn"
                    onclick="showCourierSelect(<?= $h($orderName) ?>, <?= $h($courierId) ?>)">
                    Zmień kuriera
                </button>
            <?php endif; ?>
            <strong>Courier:</strong> 
            <?= $h($packages[0]['deliveryPackage']['courierName'] ?? 'N/A') ?> 
            (ID: <?= $h($courierId) ?>)
        </p>
        
        <?php foreach ($packages as $index => $package): ?>
        <div class="package-item">
            <div class="package-details">
                <button 
                    type="button" 
                    class="change-weight-btn"
                    onclick="showWeightInput(
                        '<?= $h($orderName) ?>', 
                        '<?= $h($package['deliveryPackage']['deliveryPackageId']) ?>',
                        '<?= $h($courierId) ?>'
                    )">
                    Zmień wagę
                </button>
                <span class="package-title">Package <?= $index + 1 ?>:</span>
                <span><strong>ID:</strong> <?= $h($package['deliveryPackage']['deliveryPackageId'] ?? 'N/A') ?></span>
                <span><strong>Tracking:</strong> <?= $h($package['deliveryPackage']['deliveryShippingNumber'] ?? 'Not generated') ?></span>
                <span class="weight-display">
                    <strong>Logistic weight:</strong> 
                    <?= $h(isset($package['deliveryPackage']['deliveryPackageParameters']['deliveryWeight']) ? 
                        ($package['deliveryPackage']['deliveryPackageParameters']['deliveryWeight']) . ' g' : 'N/A') ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if ($order && $courierId): ?>
        <div class="package-actions">
            <form method="POST" class="add-package-form">
                <input type="hidden" name="order_id" value="<?= $h($orderName) ?>">
                <input type="hidden" name="courier_id" value="<?= $h($courierId) ?>">
                <input type="hidden" name="return_order" value="<?= $h($orderName) ?>">
                <button type="submit" name="add_package">Dodaj paczkę</button>
            </form>
            <a href="javascript:void(0)" 
               onclick="confirmDelete('<?= $h($orderName) ?>')" 
               class="delete-package-btn">
               Usuń paczkę
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="products-table">
        <h3>Products</h3>
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
                    <td><?= $h($product['productId']) ?></td>
                    <td><?= $h($product['productName']) ?></td>
                    <td><?= $h($product['productCode']) ?></td>
                    <td><?= $h($product['productQuantity']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>