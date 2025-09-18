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

    <div class="packages-box">
        <h3>Packages</h3>
        
        <!-- Courier Information -->
        <div class="courier-info">
            <?php if (isset($changeableCouriers[$courierId])): ?>
                <button 
                    type="button" 
                    class="change-courier-btn"
                    onclick="showCourierSelect(<?= $h($order['orderSerialNumber']) ?>, <?= $h($courierId) ?>)">
                    Zmień kuriera
                </button>
            <?php endif; ?>
            <strong>Courier:</strong> 
            <?= $h($packages[0]['deliveryPackage']['courierName'] ?? 'N/A') ?> 
            (ID: <?= $h($courierId) ?>)
        </div>

        <!-- Packages List -->
        <?php if (!empty($packages)): ?>
            <?php foreach ($packages as $index => $package): ?>
                <div class="package-item">
                    <div class="package-details">
                        <span class="package-number"><?= ($index + 1) ?>.</span>
                        <button 
                            type="button" 
                            class="change-weight-btn"
                            onclick="showWeightModal(
                                <?= $h($order['orderSerialNumber']) ?>, 
                                <?= $h($package['deliveryPackage']['deliveryPackageId']) ?>,
                                <?= $h($courierId) ?>,
                                <?= $h($package['deliveryPackage']['deliveryPackageParameters']['deliveryWeight'] ?? 1000) ?>
                            )">
                            Zmień wagę
                        </button>
                        <span class="package-title">
                            Package ID: <?= $h($package['deliveryPackage']['deliveryPackageId']) ?>
                        </span>
                        <span>
                            <strong>Weight:</strong> 
                            <?= $h($package['deliveryPackage']['deliveryPackageParameters']['deliveryWeight'] ?? 0) ?>g
                        </span>
                        <span>
                            <strong>Number:</strong> 
                            <?= $h($package['deliveryPackage']['deliveryPackageNumber'] ?: 'Not assigned') ?>
                        </span>
                        <span>
                            <strong>Shipping Number:</strong> 
                            <?= $h($package['deliveryPackage']['deliveryShippingNumber'] ?: 'Not assigned') ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Package Management Buttons -->
            <div class="package-management">
                <?php if (!$courierExists): ?>
                    <button class="add-package-btn disabled" disabled>Brak kuriera</button>
                <?php elseif (!$courierSupportsMultiplePackages): ?>
                    <button class="add-package-btn disabled" disabled>Dodaj paczkę</button>
                <?php else: ?>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="add_package" value="1">
                        <input type="hidden" name="order_id" value="<?= $h($order['orderSerialNumber']) ?>">
                        <input type="hidden" name="courier_id" value="<?= $h($courierId) ?>">
                        <input type="hidden" name="return_order" value="<?= $h($order['orderSerialNumber']) ?>">
                        <button type="submit" class="add-package-btn">Dodaj paczkę</button>
                    </form>
                    <form method="POST" class="inline-form">
                        <button type="button" class="delete-package-btn" onclick="confirmDelete(<?= $h($order['orderSerialNumber']) ?>)">
                            Usuń paczkę
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>No packages found for this order.</p>
        <?php endif; ?>
    </div>

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

<script>
function confirmDelete(orderId) {
    if (confirm('Are you sure you want to delete this package?')) {
        // Proceed with form submission to delete the package
        const form = document.createElement('form');
        form.method = 'POST';
        form.className = 'inline-form';

        const inputOrderId = document.createElement('input');
        inputOrderId.type = 'hidden';
        inputOrderId.name = 'order_id';
        inputOrderId.value = orderId;

        const inputDeletePackage = document.createElement('input');
        inputDeletePackage.type = 'hidden';
        inputDeletePackage.name = 'delete_package';
        inputDeletePackage.value = '1';

        form.appendChild(inputOrderId);
        form.appendChild(inputDeletePackage);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>