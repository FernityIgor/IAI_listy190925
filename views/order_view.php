<div class="container">
    <div class="address-boxes">
        <div class="search-container address-box">
            <form method="POST" action="index.php">
                <label for="order">Numer zamówienia:</label>
                <input type="text" id="order" name="order" value="">
                <input type="submit" value="Szukaj">
            </form>
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
            <div class="courier-info-line">
                <?php if (isset($changeableCouriers[$courierId])): ?>
                    <button 
                        type="button" 
                        class="change-courier-btn"
                        onclick="showCourierSelect(<?= $h($order['orderSerialNumber']) ?>, <?= $h($courierId) ?>)">
                        Zmień kuriera
                    </button>
                <?php endif; ?>
                <div class="courier-details">
                    <strong>Courier:</strong> 
                    <?= $h($packages[0]['deliveryPackage']['courierName'] ?? 'N/A') ?> 
                    (ID: <?= $h($courierId) ?>)
                </div>
                <button 
                    type="button" 
                    class="zlec-kuriera-btn"
                    onclick="zlecKuriera(<?= $h($order['orderSerialNumber']) ?>)">
                    Zleć kuriera
                </button>
            </div>
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
                    <!-- Hide add package button when courier doesn't support multiple packages -->
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

<!-- Modal for Zleć kuriera -->
<div id="zlecKurieraModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeZlecKurieraModal()">&times;</span>
        <h3>Zleć kuriera - Parametry przesyłki</h3>
        <form id="zlecKurieraForm">
            <div id="parametersContainer">
                <!-- Parameters will be populated by JavaScript -->
            </div>
            <div class="modal-buttons">
                <button type="button" class="modal-button cancel" onclick="closeZlecKurieraModal()">Anuluj</button>
                <button type="button" class="modal-button confirm" onclick="generujEtykiety()">Generuj</button>
            </div>
        </form>
    </div>
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

let currentOrderNumber = null;

function zlecKuriera(orderNumber) {
    console.log('zlecKuriera called with orderNumber:', orderNumber);
    currentOrderNumber = orderNumber;
    
    // Fetch package parameters via AJAX
    fetch(`?action=get_package_params&order=${orderNumber}`)
        .then(response => {
            console.log('Fetch response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            // Log the specific parameters we're interested in
            if (data.parameters) {
                data.parameters.forEach(param => {
                    if (param.key === 'privpers') {
                        console.log('PRIVPERS parameter:', {
                            key: param.key,
                            defaultValue: param.defaultValue,
                            options: param.options
                        });
                    }
                });
            }
            if (data.error) {
                alert('Błąd przy pobieraniu parametrów: ' + data.error);
                return;
            }
            
            populateParametersForm(data.parameters);
            document.getElementById('zlecKurieraModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Błąd przy pobieraniu parametrów');
        });
}

function populateParametersForm(parameters) {
    const container = document.getElementById('parametersContainer');
    container.innerHTML = '';
    
    parameters.forEach((param, index) => {
        const div = document.createElement('div');
        div.className = 'parameter-item';
        
        const label = document.createElement('label');
        label.textContent = param.name;
        div.appendChild(label);
        
        if (param.options && param.options.length > 0) {
            // Check if this is a tak/nie parameter
            const isTakNieParameter = param.options.length === 2 && 
                param.options.some(opt => opt.name === 'tak') && 
                param.options.some(opt => opt.name === 'nie');
            
            if (isTakNieParameter) {
                // Create radio buttons for tak/nie options
                const radioContainer = document.createElement('div');
                radioContainer.className = 'radio-container';
                let selectedFound = false; // Initialize selectedFound for this parameter
                
                param.options.forEach((option, optIndex) => {
                    const radioWrapper = document.createElement('div');
                    radioWrapper.className = 'radio-option';
                    
                    const radioInput = document.createElement('input');
                    radioInput.type = 'radio';
                    radioInput.name = param.key;
                    radioInput.value = option.id;
                    radioInput.id = `${param.key}_${option.id}`;
                    
                    const radioLabel = document.createElement('label');
                    radioLabel.htmlFor = radioInput.id;
                    radioLabel.textContent = option.name;
                    
                    // Set default value
                    let defaultValueStr = param.defaultValue !== undefined ? String(param.defaultValue).trim() : '';
                    const optionIdStr = String(option.id).trim();
                    
                    // Debug logging for SMS field specifically
                    if (param.key === 'SMS') {
                        console.log(`DEBUG SMS: param.key=${param.key}, defaultValue="${defaultValueStr}", option.id="${optionIdStr}", option.name="${option.name}"`);
                    }
                    
                    // Handle different formats of boolean values
                    let isMatch = false;
                    if (defaultValueStr === optionIdStr) {
                        isMatch = true;
                        if (param.key === 'SMS') {
                            console.log(`DEBUG SMS: Direct match - defaultValue="${defaultValueStr}" === optionId="${optionIdStr}"`);
                        }
                    } else {
                        // Handle yes/no vs 0/1 mappings
                        const valueMap = {
                            'yes': ['1', 'tak'],
                            'no': ['0', 'nie'],
                            '1': ['yes', 'tak'],
                            '0': ['no', 'nie'],
                            'y': ['yes', '1', 'tak'],
                            'n': ['no', '0', 'nie']
                        };
                        
                        if (valueMap[defaultValueStr] && valueMap[defaultValueStr].includes(optionIdStr)) {
                            isMatch = true;
                            if (param.key === 'SMS') {
                                console.log(`DEBUG SMS: Mapped match 1 - defaultValue="${defaultValueStr}" maps to optionId="${optionIdStr}"`);
                            }
                        } else if (valueMap[optionIdStr] && valueMap[optionIdStr].includes(defaultValueStr)) {
                            isMatch = true;
                            if (param.key === 'SMS') {
                                console.log(`DEBUG SMS: Mapped match 2 - optionId="${optionIdStr}" maps to defaultValue="${defaultValueStr}"`);
                            }
                        }
                    }
                    
                    // Override for privpers to match API documentation (temporary fix)
                    if (param.key === 'privpers' && defaultValueStr === '0') {
                        console.log('Overriding privpers defaultValue from 0 to 1 to match API spec');
                        isMatch = (optionIdStr === '1');
                    }
                    
                    // Override for SMS to match API documentation (temporary fix)
                    if (param.key === 'SMS' && defaultValueStr === '0') {
                        console.log('Overriding SMS defaultValue from 0 to yes to match API spec');
                        isMatch = (optionIdStr === 'yes');
                    }
                    
                    if (isMatch) {
                        radioInput.checked = true;
                        selectedFound = true;
                        
                        if (param.key === 'SMS') {
                            console.log(`DEBUG SMS: SETTING CHECKED - ${param.key} to ${option.name} (defaultValue: "${defaultValueStr}", optionId: "${optionIdStr}")`);
                        }
                        console.log(`DEBUG: Setting ${param.key} to ${option.name} (defaultValue: "${defaultValueStr}", optionId: "${optionIdStr}")`);
                    } else if (param.key === 'additionalHandling' || param.key === 'SMS') {
                        console.log(`DEBUG ${param.key}: NOT selecting ${option.name} (defaultValue: "${defaultValueStr}", optionId: "${optionIdStr}")`);
                    }
                    
                    radioWrapper.appendChild(radioInput);
                    radioWrapper.appendChild(radioLabel);
                    radioContainer.appendChild(radioWrapper);
                });
                
                // If no option was selected, default to "nie" (0)
                if (!selectedFound) {
                    const nieRadio = radioContainer.querySelector('input[value="0"]');
                    if (nieRadio) {
                        nieRadio.checked = true;
                    }
                }
                
                div.appendChild(radioContainer);
            } else {
                // Create select dropdown for other options (like service type)
                const select = document.createElement('select');
                select.name = param.key;
                select.className = 'parameter-select';
                
                param.options.forEach((option, optIndex) => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.id;
                    optionElement.textContent = option.name;
                    
                    // Convert both to strings for comparison
                    const defaultValueStr = String(param.defaultValue || '');
                    const optionIdStr = String(option.id);
                    
                    // Set default value if it matches
                    if (param.defaultValue !== undefined && param.defaultValue !== null && param.defaultValue !== "" && defaultValueStr === optionIdStr) {
                        optionElement.selected = true;
                        selectedFound = true;
                    }
                    
                    select.appendChild(optionElement);
                });
                
                div.appendChild(select);
            }
        } else {
            // Create text input for text parameters
            const input = document.createElement('input');
            input.type = 'text';
            input.name = param.key;
            input.className = 'parameter-input';
            input.value = param.defaultValue || '';
            div.appendChild(input);
        }
        
        container.appendChild(div);
    });
}

function generujEtykiety() {
    if (!currentOrderNumber) {
        alert('Błąd: Brak numeru zamówienia');
        return;
    }
    
    // Collect form data
    const form = document.getElementById('zlecKurieraForm');
    const formData = new FormData(form);
    const parameters = {};
    
    for (let [key, value] of formData.entries()) {
        parameters[key] = value;
    }
    
    // Send request to generate labels
    const requestData = new FormData();
    requestData.append('action', 'generate_labels');
    requestData.append('order_id', currentOrderNumber);
    requestData.append('parameters', JSON.stringify(parameters));
    
    fetch('generate_labels.php', {
        method: 'POST',
        body: requestData
    })
    .then(response => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Server response (non-JSON):', text);
                throw new Error('Server returned invalid JSON response');
            }
        });
    })
    .then(data => {
        if (data.success) {
            alert('Etykiety zostały wygenerowane pomyślnie!');
            closeZlecKurieraModal();
            // Reload the page to see updated package information
            window.location.reload();
        } else {
            alert('Błąd przy generowaniu etykiet: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Błąd przy generowaniu etykiet: ' + error.message);
    });
}

function closeZlecKurieraModal() {
    document.getElementById('zlecKurieraModal').style.display = 'none';
    currentOrderNumber = null;
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('zlecKurieraModal');
    if (event.target == modal) {
        closeZlecKurieraModal();
    }
}
</script>