<div class="container">
    <div class="address-boxes">
        <div class="search-container address-box">
            <form method="POST" action="index.php">
                <label for="order">Numer zamówienia:</label>
                <input type="text" id="order" name="order" value="<?= isset($orderName) ? $h($orderName) : '' ?>">
                <?php if (!empty($wfmagOrder)): ?>
                <input type="hidden" name="wfmag" value="<?= $h($wfmagOrder) ?>">
                <?php endif; ?>
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
        <div class="order-details-header">
            <h3>Order Details</h3>
            <div class="email-buttons">
                <button 
                    type="button" 
                    class="email-btn bok-btn"
                    onclick="sendEmailToBOK(<?= $h($order['orderSerialNumber']) ?>, '<?= $h($wfmagOrder ?? '') ?>')">
                    Zgłoś do BOK
                </button>
                <button 
                    type="button" 
                    class="email-btn manager-btn"
                    onclick="sendEmailToManager(<?= $h($order['orderSerialNumber']) ?>, '<?= $h($wfmagOrder ?? '') ?>')">
                    Zgłoś do kierownika
                </button>
            </div>
        </div>
        <?php 
        $shopId = $order['orderDetails']['orderSourceResults']['shopId'] ?? null;
        $shopName = $shopNames[$shopId] ?? 'Unknown Shop';
        ?>
        <p><strong>Shop:</strong> <?= $h($shopName) ?> (ID: <?= $h($shopId) ?>)</p>
        <?php if (!empty($wfmagOrder)): ?>
        <p class="wfmag-order"><strong>Wfmag order:</strong> <?= $h($wfmagOrder) ?></p>
        <?php endif; ?>
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
                <div class="courier-details">
                    <strong>Current Courier:</strong> 
                    <?= $h($packages[0]['deliveryPackage']['courierName'] ?? 'N/A') ?> 
                    (ID: <?= $h($courierId) ?>)
                    <?php if ($courierId == 26): ?>
                        <span class="special-courier-badge">Special Courier</span>
                    <?php endif; ?>
                </div>
                <?php if (isset($changeableCouriers[$courierId])): ?>
                    <div class="courier-selection">
                        <?php if ($courierId == 26): ?>
                            <strong>Change from K-EX to:</strong>
                            <span class="courier-note">(K-EX can be changed but not selected again)</span>
                        <?php else: ?>
                            <strong>Change to:</strong>
                        <?php endif; ?>
                        <?php foreach ($changeableCouriers as $id => $name): ?>
                            <?php 
                            // Don't show current courier
                            if ($id == $courierId) continue;
                            
                            // Special rule: Don't show K-EX (ID 26) as an option when current courier is NOT K-EX
                            if ($id == 26 && $courierId != 26) continue;
                            ?>
                                <button 
                                    type="button"
                                    class="direct-courier-btn"
                                    onclick="selectCourierDirect(<?= $h($order['orderSerialNumber']) ?>, <?= $h($id) ?>)">
                                    <?= $h($name) ?>
                                </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?></div>
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
                            onclick="showWeightInput(<?= $h($order['orderSerialNumber']) ?>, <?= $h($package['deliveryPackage']['deliveryPackageId']) ?>, <?= $h($courierId) ?>)">
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

    <!-- Courier Parameters Section (Always Visible) -->
    <div class="courier-parameters-box">
        <div class="courier-params-header">
            <h3>Parametry przesyłki</h3>
            <div class="courier-action-buttons">
                <div class="save-option-container" title="Zaznacz, aby wybrać miejsce zapisu w przeglądarce. Bez zaznaczenia pliki zostaną zapisane do domyślnego folderu.">
                    <label class="save-option-label">
                        <input type="checkbox" id="chooseSaveLocation" class="save-option-checkbox">
                        <span class="save-option-text">Wybierz miejsce zapisu</span>
                    </label>
                </div>
                <button 
                    type="button" 
                    class="courier-action-btn generate-btn"
                    onclick="generateLabels()"
                    id="generateBtn">
                    Generuj etykiety
                </button>
                <button 
                    type="button" 
                    class="courier-action-btn generate-download-btn"
                    onclick="generateAndSave()"
                    id="generateDownloadBtn">
                    Generuj i zapisz
                </button>
                <button 
                    type="button" 
                    class="courier-action-btn generate-download-print-btn"
                    onclick="generateSaveAndPrint()"
                    id="generateDownloadPrintBtn">
                    Generuj, zapisz i drukuj
                </button>
                <button 
                    type="button" 
                    class="courier-action-btn download-labels-btn"
                    onclick="checkCheckboxAndSave(<?= $h($order['orderSerialNumber']) ?>)">
                    Pobierz etykiety
                </button>
                <button 
                    type="button" 
                    class="courier-action-btn print-custom-btn"
                    onclick="openFilePicker()">
                    Drukuj
                </button>
            </div>
        </div>
        
        <form id="courierParametersForm">
            <div id="courierParametersContainer" class="parameters-grid">
                <!-- Parameters will be populated by JavaScript when page loads or courier changes -->
                <div class="loading-message">Ładowanie parametrów kuriera...</div>
            </div>
        </form>
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

<!-- Order Search Section -->
<div class="order-search-section">
    <h3>Wyszukaj zamówienie</h3>
    <div class="search-form">
        <input type="text" id="searchInput" placeholder="Wprowadź kod etykiety lub numer zlecenia..." class="search-input">
        <button type="button" onclick="searchOrders()" class="search-button" id="searchBtn">Szukaj</button>
    </div>
</div>

<!-- Modal for Zleć kuriera -->
<div id="zlecKurieraModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeZlecKurieraModal()">&times;</span>
        <h3>Zleć kuriera - Parametry przesyłki</h3>
        
        <!-- Top buttons -->
        <div class="modal-buttons top-buttons">
            <button type="button" class="modal-button cancel" onclick="closeZlecKurieraModal()">Anuluj</button>
            <button type="button" class="modal-button confirm" onclick="generujEtykiety()">Generuj</button>
            <button type="button" class="modal-button generate-download" onclick="generujIPobierz()">Generuj i pobierz</button>
        </div>
        
        <form id="zlecKurieraForm">
            <div id="parametersContainer">
                <!-- Parameters will be populated by JavaScript -->
            </div>
            <div class="modal-buttons bottom-buttons">
                <button type="button" class="modal-button cancel" onclick="closeZlecKurieraModal()">Anuluj</button>
                <button type="button" class="modal-button confirm" onclick="generujEtykiety()">Generuj</button>
                <button type="button" class="modal-button generate-download" onclick="generujIPobierz()">Generuj i pobierz</button>
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
let currentWfmagOrder = null;

// Function to load courier parameters automatically
function loadCourierParameters(orderNumber, wfmagOrder) {
    console.log('Loading courier parameters for order:', orderNumber);
    currentOrderNumber = orderNumber;
    currentWfmagOrder = wfmagOrder || '';
    
    const container = document.getElementById('courierParametersContainer');
    container.innerHTML = '<div class="loading-message">Ładowanie parametrów kuriera...</div>';
    
    // Fetch package parameters via AJAX
    fetch(`?action=get_package_params&order=${orderNumber}`)
        .then(response => {
            console.log('Fetch response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Received courier parameters:', data);
            if (data.error) {
                container.innerHTML = '<div class="error-message">Błąd przy pobieraniu parametrów: ' + data.error + '</div>';
                return;
            }
            
            if (data.parameters) {
                populateParametersForm(data.parameters);
            } else {
                container.innerHTML = '<div class="no-params-message">Brak dostępnych parametrów dla tego kuriera.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading courier parameters:', error);
            container.innerHTML = '<div class="error-message">Błąd przy pobieraniu parametrów kuriera.</div>';
        });
}

function zlecKuriera(orderNumber, wfmagOrder) {
    console.log('zlecKuriera called with orderNumber:', orderNumber, 'wfmagOrder:', wfmagOrder);
    currentOrderNumber = orderNumber;
    currentWfmagOrder = wfmagOrder || '';
    
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
    const container = document.getElementById('courierParametersContainer');
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

// Helper function to process parameters and append wfmag to [iai:order_sn] values
function processParametersWithWfmag(parameters, wfmagOrder, orderNumber) {
    if (!wfmagOrder || wfmagOrder.trim() === '') {
        return parameters; // No wfmag to append
    }
    
    const processedParameters = {};
    let modificationsCount = 0;
    
    for (const [key, value] of Object.entries(parameters)) {
        if (typeof value === 'string' && value.includes('[iai:order_sn]')) {
            // Replace entire placeholder with custom text including wfmag
            processedParameters[key] = `IAI: ${orderNumber}, wfmag: ${wfmagOrder.trim()}`;
            console.log(`DEBUG: Replaced [iai:order_sn] with custom text for ${key}: "${value}" -> "${processedParameters[key]}"`);
            modificationsCount++;
        } else if (typeof value === 'string' && value.includes('[iai:delivery_notice]')) {
            // Replace entire placeholder with custom text including wfmag  
            processedParameters[key] = `IAI: ${orderNumber}, wfmag: ${wfmagOrder.trim()}`;
            console.log(`DEBUG: Replaced [iai:delivery_notice] with custom text for ${key}: "${value}" -> "${processedParameters[key]}"`);
            modificationsCount++;
        } else {
            processedParameters[key] = value;
        }
    }
    
    console.log(`DEBUG: Total modifications made: ${modificationsCount}`);
    
    return processedParameters;
}

function generujEtykiety() {
    if (!currentOrderNumber) {
        alert('Błąd: Brak numeru zamówienia');
        return;
    }
    
    const generateBtn = document.getElementById('generateBtn');
    const originalText = generateBtn.textContent;
    
    // Set loading state  
    generateBtn.textContent = 'Generowanie...';
    generateBtn.disabled = true;
    
    // Collect form data
    const form = document.getElementById('courierParametersForm');
    const formData = new FormData(form);
    const rawParameters = {};
    
    for (let [key, value] of formData.entries()) {
        rawParameters[key] = value;
    }
    
    // Process parameters to append wfmag to [iai:order_sn] values
    const parameters = processParametersWithWfmag(rawParameters, currentWfmagOrder, currentOrderNumber);
    
    // Send request to generate labels only
    const requestData = new FormData();
    requestData.append('action', 'generate_labels');
    requestData.append('order_id', currentOrderNumber);
    requestData.append('parameters', JSON.stringify(parameters));
    
    fetch('../public/generate_labels.php', {
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
            alert('Etykiety zostały wygenerowane pomyślnie w systemie Idosell!\n\nTeraz możesz użyć przycisku "Pobierz etykiety" aby je zapisać.');
            // Reload the page to see updated package information
            window.location.reload();
        } else {
            // Show detailed error information
            let errorMessage = 'Błąd przy generowaniu etykiet:\n\n';
            
            if (data.error) {
                errorMessage += 'Błąd: ' + data.error + '\n';
            }
            
            if (data.http_status) {
                errorMessage += 'Status HTTP: ' + data.http_status + '\n';
            }
            
            if (data.error_code) {
                errorMessage += 'Kod błędu: ' + data.error_code + '\n';
            }
            
            // Add suggestions based on error type
            if (data.error && data.error.includes('już wygenerowana')) {
                errorMessage += '\n💡 Sugestia: Usuń istniejącą etykietę przed wygenerowaniem nowej.';
            } else if (data.http_status === 207) {
                errorMessage += '\n💡 Sugestia: Sprawdź ustawienia parametrów kuriera (np. "Wnosić paczki" na "nie").';
            }
            
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Błąd przy generowaniu etykiet: ' + error.message);
    })
    .finally(() => {
        // Restore button state
        generateBtn.textContent = originalText;
        generateBtn.disabled = false;
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

// ========================================
// MODULAR FUNCTIONS AS REQUESTED
// ========================================

// 1. Generate labels only (working well)
function generateLabels() {
    if (!currentOrderNumber) {
        alert('Błąd: Brak numeru zamówienia');
        return;
    }
    
    const generateBtn = document.getElementById('generateBtn');
    const originalText = generateBtn.textContent;
    
    generateBtn.textContent = 'Generowanie...';
    generateBtn.disabled = true;
    
    // Collect form data
    const form = document.getElementById('courierParametersForm');
    const formData = new FormData(form);
    const rawParameters = {};
    
    for (let [key, value] of formData.entries()) {
        rawParameters[key] = value;
    }
    
    const parameters = processParametersWithWfmag(rawParameters, currentWfmagOrder, currentOrderNumber);
    
    // Send request to generate labels only
    const requestData = new FormData();
    requestData.append('action', 'generate_labels');
    requestData.append('order_id', currentOrderNumber);
    requestData.append('parameters', JSON.stringify(parameters));

    fetch('../public/generate_labels.php', {
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
            alert('Etykiety zostały wygenerowane pomyślnie w systemie Idosell!');
            window.location.reload();
        } else {
            let errorMessage = 'Błąd przy generowaniu etykiet:\n\n';
            if (data.error) {
                errorMessage += 'Błąd: ' + data.error + '\n';
            }
            if (data.http_status) {
                errorMessage += 'Status HTTP: ' + data.http_status + '\n';
            }
            if (data.error_code) {
                errorMessage += 'Kod błędu: ' + data.error_code + '\n';
            }
            if (data.error && data.error.includes('już wygenerowana')) {
                errorMessage += '\n💡 Sugestia: Usuń istniejącą etykietę przed wygenerowaniem nowej.';
            } else if (data.http_status === 207) {
                errorMessage += '\n💡 Sugestia: Sprawdź ustawienia parametrów kuriera.';
            }
            alert(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Błąd przy generowaniu etykiet: ' + error.message);
    })
    .finally(() => {
        generateBtn.textContent = originalText;
        generateBtn.disabled = false;
    });
}

// 2. Save without checkbox (to config directory)
function saveWithoutCheckbox(orderNumber) {
    return fetch('../public/save_labels.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_number=${orderNumber}`
    })
    .then(response => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error. Raw response:', text);
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
            }
        });
    })
    .then(data => {
        if (data.success) {
            let message = 'Etykiety zostały zapisane do folderu!';
            if (data.files && data.files.length > 0) {
                message += '\n\nZapisane pliki:';
                data.files.forEach(file => {
                    message += `\n- ${file.filename}`;
                });
            }
            if (data.directory) {
                message += `\n\nLokalizacja: ${data.directory}`;
            }
            alert(message);
            return true;
        } else {
            throw new Error(data.error || 'Failed to save labels');
        }
    });
}

// 3. Save with checkbox (to browser)
function saveWithCheckbox(orderNumber) {
    return new Promise((resolve, reject) => {
        // Create form for direct download to browser
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'download_labels.php';
        form.target = '_blank'; // Open in new tab for download
        form.style.display = 'none';

        const orderInput = document.createElement('input');
        orderInput.type = 'hidden';
        orderInput.name = 'order_number';
        orderInput.value = orderNumber;

        form.appendChild(orderInput);
        document.body.appendChild(form);
        
        // Submit form to trigger download
        form.submit();
        
        // Clean up and resolve after a short delay
        setTimeout(() => {
            document.body.removeChild(form);
            resolve(true);
        }, 1000);
    });
}

// 4. Check checkbox and save appropriately
function checkCheckboxAndSave(orderNumber) {
    console.log('checkCheckboxAndSave called with orderNumber:', orderNumber);
    
    if (!orderNumber) {
        alert('Błąd: Brak numeru zamówienia');
        return;
    }

    // Show loading state on button
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Pobieranie...';
    button.disabled = true;

    // Check checkbox state
    const chooseSaveLocation = document.getElementById('chooseSaveLocation').checked;
    
    let savePromise;
    
    if (chooseSaveLocation) {
        console.log('Checkbox checked: saving to browser');
        savePromise = saveWithCheckbox(orderNumber);
    } else {
        console.log('Checkbox unchecked: saving to config directory');
        savePromise = saveWithoutCheckbox(orderNumber);
    }
    
    savePromise
    .then(() => {
        // Success handled in individual save functions
    })
    .catch(error => {
        console.error('Save error:', error);
        alert('Błąd podczas zapisywania etykiet: ' + error.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

// 5. Combined function: Generate + Save
function generateAndSave() {
    if (!currentOrderNumber) {
        alert('Błąd: Brak numeru zamówienia');
        return;
    }
    
    const generateDownloadBtn = document.getElementById('generateDownloadBtn');
    const originalText = generateDownloadBtn.textContent;
    
    generateDownloadBtn.textContent = 'Generowanie...';
    generateDownloadBtn.disabled = true;
    
    // First generate labels
    const form = document.getElementById('courierParametersForm');
    const formData = new FormData(form);
    const rawParameters = {};
    
    for (let [key, value] of formData.entries()) {
        rawParameters[key] = value;
    }
    
    const parameters = processParametersWithWfmag(rawParameters, currentWfmagOrder, currentOrderNumber);
    
    const requestData = new FormData();
    requestData.append('action', 'generate_labels');
    requestData.append('order_id', currentOrderNumber);
    requestData.append('parameters', JSON.stringify(parameters));

    fetch('../public/generate_labels.php', {
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
            generateDownloadBtn.textContent = 'Zapisywanie...';
            
            // Then check checkbox and save
            const chooseSaveLocation = document.getElementById('chooseSaveLocation').checked;
            
            if (chooseSaveLocation) {
                return saveWithCheckbox(currentOrderNumber);
            } else {
                return saveWithoutCheckbox(currentOrderNumber);
            }
        } else {
            let errorMessage = 'Błąd przy generowaniu etykiet:\n\n';
            if (data.error) {
                errorMessage += 'Błąd: ' + data.error + '\n';
            }
            throw new Error(errorMessage);
        }
    })
    .then(() => {
        // Success message already shown by saveWithCheckbox/saveWithoutCheckbox functions
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Błąd: ' + error.message);
    })
    .finally(() => {
        generateDownloadBtn.textContent = originalText;
        generateDownloadBtn.disabled = false;
    });
}

// 6. Combined function: Generate + Save + Print
function generateSaveAndPrint() {
    if (!currentOrderNumber) {
        alert('Błąd: Brak numeru zamówienia');
        return;
    }
    
    const generateDownloadPrintBtn = document.getElementById('generateDownloadPrintBtn');
    const originalText = generateDownloadPrintBtn.textContent;
    
    generateDownloadPrintBtn.textContent = 'Generowanie...';
    generateDownloadPrintBtn.disabled = true;
    
    // First generate labels
    const form = document.getElementById('courierParametersForm');
    const formData = new FormData(form);
    const rawParameters = {};
    
    for (let [key, value] of formData.entries()) {
        rawParameters[key] = value;
    }
    
    const parameters = processParametersWithWfmag(rawParameters, currentWfmagOrder, currentOrderNumber);
    
    const requestData = new FormData();
    requestData.append('action', 'generate_labels');
    requestData.append('order_id', currentOrderNumber);
    requestData.append('parameters', JSON.stringify(parameters));

    fetch('../public/generate_labels.php', {
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
            generateDownloadPrintBtn.textContent = 'Zapisywanie...';
            
            // Check checkbox state and save accordingly
            const chooseSaveLocation = document.getElementById('chooseSaveLocation').checked;
            
            if (chooseSaveLocation) {
                // If checkbox is checked, save to browser download and show message
                alert('Uwaga: Dla funkcji drukowania plik zostanie zapisany do domyślnego folderu, nie do przeglądarki.');
                return saveWithoutCheckbox(currentOrderNumber);
            } else {
                // Save to configured directory (needed for printing)
                return saveWithoutCheckbox(currentOrderNumber);
            }
        } else {
            let errorMessage = 'Błąd przy generowaniu etykiet:\n\n';
            if (data.error) {
                errorMessage += 'Błąd: ' + data.error + '\n';
            }
            throw new Error(errorMessage);
        }
    })
    .then((saveResult) => {
        generateDownloadPrintBtn.textContent = 'Drukowanie...';
        
        // Now print the saved file using the information from save result
        return printLabels(currentOrderNumber, saveResult);
    })
    .then(() => {
        alert('Etykiety zostały wygenerowane, zapisane i wysłane do drukarki!');
        window.location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Błąd: ' + error.message);
    })
    .finally(() => {
        generateDownloadPrintBtn.textContent = originalText;
        generateDownloadPrintBtn.disabled = false;
    });
}

// 7. Print labels function
function printLabels(orderNumber, saveResult) {
    return fetch('../public/print_labels.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_number=${orderNumber}`
    })
    .then(response => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error. Raw response:', text);
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
            }
        });
    })
    .then(data => {
        if (data.success) {
            console.log('Printing completed successfully');
            return true;
        } else {
            throw new Error(data.error || 'Failed to print labels');
        }
    });
}

// 8. Custom file picker and print function
function openFilePicker() {
    // Create hidden file input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.pdf';
    fileInput.style.display = 'none';
    
    fileInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file && file.type === 'application/pdf') {
            printCustomFile(file);
        } else if (file) {
            alert('Proszę wybrać plik PDF.');
        }
        // Clean up
        document.body.removeChild(fileInput);
    });
    
    // Add to DOM and trigger click
    document.body.appendChild(fileInput);
    fileInput.click();
}

function printCustomFile(file) {
    const printBtn = event.target;
    const originalText = printBtn.textContent;
    
    printBtn.textContent = 'Drukowanie...';
    printBtn.disabled = true;
    
    // Create FormData and upload file
    const formData = new FormData();
    formData.append('pdf_file', file);
    
    fetch('../public/print_custom_file.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error. Raw response:', text);
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
            }
        });
    })
    .then(data => {
        if (data.success) {
            alert(`Plik "${file.name}" został wysłany do drukarki!`);
        } else {
            throw new Error(data.error || 'Failed to print file');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Błąd podczas drukowania: ' + error.message);
    })
    .finally(() => {
        printBtn.textContent = originalText;
        printBtn.disabled = false;
    });
}

// ========================================
// OLD FUNCTION (keeping for compatibility)
// ========================================

// Function to download labels for an order
function pobierzEtykiety(orderNumber) {
    console.log('pobierzEtykiety called with orderNumber:', orderNumber);
    
    if (!orderNumber) {
        alert('Błąd: Brak numeru zamówienia');
        return;
    }

    // Show loading state on button
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Pobieranie...';
    button.disabled = true;

    // Check if user wants to choose save location
    const chooseSaveLocation = document.getElementById('chooseSaveLocation').checked;
    
    if (chooseSaveLocation) {
        console.log('Downloading to browser (user choice)...');
        
        // Create form for direct download to browser
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'download_labels.php';
        form.style.display = 'none';

        const orderInput = document.createElement('input');
        orderInput.type = 'hidden';
        orderInput.name = 'order_number';
        orderInput.value = orderNumber;

        form.appendChild(orderInput);
        document.body.appendChild(form);
        
        // Submit form to trigger download
        form.submit();
        
        // Clean up
        setTimeout(() => {
            document.body.removeChild(form);
            button.textContent = originalText;
            button.disabled = false;
        }, 1000);
        
    } else {
        console.log('Downloading to configured directory...');
        
        // Download to configured directory using save_labels.php
        fetch('../public/save_labels.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_number=${orderNumber}`
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error. Raw response:', text);
                    throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
                }
            });
        })
        .then(data => {
            if (data.success) {
                let message = 'Etykiety zostały pobrane i zapisane!';
                if (data.files && data.files.length > 0) {
                    message += '\n\nZapisane pliki:';
                    data.files.forEach(file => {
                        message += `\n- ${file.filename}`;
                    });
                }
                if (data.directory) {
                    message += `\n\nLokalizacja: ${data.directory}`;
                }
                alert(message);
            } else {
                throw new Error(data.error || 'Failed to save labels');
            }
        })
        .catch(error => {
            console.error('Download error:', error);
            alert('Błąd podczas pobierania etykiet: ' + error.message);
        })
        .finally(() => {
            button.textContent = originalText;
            button.disabled = false;
        });
    }
}

// Function to generate labels and immediately download them
function generujIPobierz() {
    console.log('generujIPobierz called');
    
    // Disable all buttons during the process
    const generateBtn = document.getElementById('generateBtn');
    const generateDownloadBtn = document.getElementById('generateDownloadBtn');
    
    const originalGenerateText = generateBtn.textContent;
    const originalGenerateDownloadText = generateDownloadBtn.textContent;
    
    // Set loading state
    generateBtn.textContent = 'Generowanie...';
    generateBtn.disabled = true;
    generateDownloadBtn.textContent = 'Generowanie...';
    generateDownloadBtn.disabled = true;
    
    // Get current order number from global variable or extract from UI
    const orderNumber = currentOrderNumber; // This should be set when modal opens
    
    console.log('Current order number:', orderNumber);
    console.log('Current order number type:', typeof orderNumber);
    
    if (!orderNumber) {
        alert('Błąd: Brak numeru zamówienia. currentOrderNumber = ' + currentOrderNumber);
        restoreButtons();
        return;
    }
    
    // Step 1: Generate labels (same as generujEtykiety but without modal close)
    const form = document.getElementById('courierParametersForm');
    const formData = new FormData();
    
    // Collect all form parameters
    const inputs = form.querySelectorAll('input, select, textarea');
    const rawParameters = {};
    
    inputs.forEach(input => {
        if (input.type === 'radio') {
            if (input.checked) {
                rawParameters[input.name] = input.value;
            }
        } else if (input.type === 'checkbox') {
            rawParameters[input.name] = input.checked ? input.value : '';
        } else if (input.name && input.value !== '') {
            rawParameters[input.name] = input.value;
        }
    });
    
    // Process parameters to append wfmag to [iai:order_sn] values
    const parameters = processParametersWithWfmag(rawParameters, currentWfmagOrder, orderNumber);
    
    formData.append('action', 'generate_labels');
    formData.append('order_id', orderNumber);
    formData.append('parameters', JSON.stringify(parameters));
    
    console.log('Step 1: Generating labels...');
    
    // Step 1: Generate labels
    fetch('../public/generate_labels.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(generateData => {
        console.log('Generate response:', generateData);
        
        if (generateData.success) {
            // Update button text for download phase
            const generateDownloadBtn = document.getElementById('generateDownloadBtn');
            if (generateDownloadBtn) {
                generateDownloadBtn.textContent = 'Pobieranie...';
            }
            
            // Check if user wants to choose save location
            const chooseSaveLocation = document.getElementById('chooseSaveLocation').checked;
            
            if (chooseSaveLocation) {
                console.log('Step 2: Generating and downloading to browser (user choice)...');
                
                // User chose to select save location - generate and download in one step
                restoreButtons();
                
                // Create and submit a form for direct generation + download
                const downloadForm = document.createElement('form');
                downloadForm.method = 'POST';
                downloadForm.action = 'generate_and_download.php';
                downloadForm.target = '_blank'; // Open download in new tab
                
                // Add order number
                const orderInput = document.createElement('input');
                orderInput.type = 'hidden';
                orderInput.name = 'order_number';
                orderInput.value = orderNumber;
                downloadForm.appendChild(orderInput);
                
                // Add parameters
                const paramsInput = document.createElement('input');
                paramsInput.type = 'hidden';
                paramsInput.name = 'parameters';
                paramsInput.value = JSON.stringify(parameters);
                downloadForm.appendChild(paramsInput);
                
                document.body.appendChild(downloadForm);
                downloadForm.submit();
                document.body.removeChild(downloadForm);
                
                alert('Etykiety zostały wygenerowane i pobrane do przeglądarki!');
                window.location.reload();
                
            } else {
                console.log('Step 2: Generating and saving to configured directory...');
                
                // Default behavior - generate and save to configured directory in one step
                fetch('../public/generate_and_save.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `order_number=${orderNumber}&parameters=${encodeURIComponent(JSON.stringify(parameters))}`
                })
                .then(response => {
                    console.log('Save response status:', response.status);
                    return response.text().then(text => {
                        console.log('Save response text:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('JSON parse error. Raw response:', text);
                            throw new Error('Server returned invalid JSON. Check server logs. Response: ' + text.substring(0, 200));
                        }
                    });
                })
                .then(saveData => {
                    console.log('Save response:', saveData);
                    
                    if (saveData.success) {
                        let message = 'Etykiety zostały wygenerowane i zapisane!';
                        if (saveData.files && saveData.files.length > 0) {
                            message += '\n\nZapisane pliki:';
                            saveData.files.forEach(file => {
                                message += `\n- ${file.filename}`;
                            });
                        }
                        if (saveData.directory) {
                            message += `\n\nLokalizacja: ${saveData.directory}`;
                        }
                        
                        alert(message);
                        restoreButtons();
                        window.location.reload();
                    } else {
                        throw new Error(saveData.error || 'Failed to save labels');
                    }
                })
                .catch(saveError => {
                    console.error('Save error:', saveError);
                    
                    // Try to get more detailed error information
                    let errorMessage = 'Błąd podczas zapisywania etykiet: ' + saveError.message;
                    
                    // If it's a JSON parse error, the server likely returned HTML
                    if (saveError.message.includes('JSON') || saveError.message.includes('Unexpected token')) {
                        errorMessage = 'Błąd serwera - sprawdź konfigurację ścieżki zapisu w Docker/Linux';
                    }
                    
                    alert(errorMessage);
                    restoreButtons();
                });
            }
            
            return; // End the promise chain here since we're using form submission
            
        } else {
            // Show detailed error information for generation step
            let errorMessage = 'Generowanie etykiet nie powiodło się:\n\n';
            
            if (generateData.error) {
                errorMessage += 'Błąd: ' + generateData.error + '\n';
            }
            
            if (generateData.http_status) {
                errorMessage += 'Status HTTP: ' + generateData.http_status + '\n';
            }
            
            if (generateData.error_code) {
                errorMessage += 'Kod błędu: ' + generateData.error_code + '\n';
            }
            
            // Add suggestions based on error type
            if (generateData.error && generateData.error.includes('już wygenerowana')) {
                errorMessage += '\n💡 Sugestia: Usuń istniejącą etykietę przed wygenerowaniem nowej.';
            } else if (generateData.http_status === 207) {
                errorMessage += '\n💡 Sugestia: Sprawdź ustawienia parametrów kuriera (np. "Wnosić paczki" na "nie").';
            }
            
            restoreButtons();
            throw new Error(errorMessage);
        }
    })
    .catch(error => {
        console.error('Error in generujIPobierz:', error);
        restoreButtons();
        alert('Błąd: ' + error.message);
    });
    
    // Helper function to restore button states
    function restoreButtons() {
        generateBtn.textContent = originalGenerateText;
        generateBtn.disabled = false;
        generateDownloadBtn.textContent = originalGenerateDownloadText;
        generateDownloadBtn.disabled = false;
    }
}



// Function to send email to BOK
function sendEmailToBOK(orderNumber, wfmagOrder) {
    const email = 'info@fernity.com';
    
    // Build subject with both order numbers
    let subject = `błąd w zamówieniu ${orderNumber}`;
    if (wfmagOrder && wfmagOrder.trim() !== '') {
        subject += ` (Wfmag: ${wfmagOrder})`;
    }
    
    // Build body with both order numbers
    let body = `Dotyczy zamówienia numer: ${orderNumber}`;
    if (wfmagOrder && wfmagOrder.trim() !== '') {
        body += `\nWfmag order: ${wfmagOrder}`;
    }
    body += `\n\nOpis problemu:\n\n\n\nPozdrawiam`;
    
    openGmailCompose(email, subject, body);
}

// Function to send email to Manager
function sendEmailToManager(orderNumber, wfmagOrder) {
    const email = 'magazyn@fernity.com';
    
    // Build subject with both order numbers
    let subject = `błąd w zamówieniu ${orderNumber}`;
    if (wfmagOrder && wfmagOrder.trim() !== '') {
        subject += ` (Wfmag: ${wfmagOrder})`;
    }
    
    // Build body with both order numbers
    let body = `Dotyczy zamówienia numer: ${orderNumber}`;
    if (wfmagOrder && wfmagOrder.trim() !== '') {
        body += `\nWfmag order: ${wfmagOrder}`;
    }
    body += `\n\nOpis problemu:\n\n\n\nPozdrawiam`;
    
    openGmailCompose(email, subject, body);
}

// Helper function to open Gmail compose window
function openGmailCompose(email, subject, body) {
    // Encode the parameters for URL
    const encodedEmail = encodeURIComponent(email);
    const encodedSubject = encodeURIComponent(subject);
    const encodedBody = encodeURIComponent(body);
    
    // Create Gmail compose URL
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodedEmail}&su=${encodedSubject}&body=${encodedBody}`;
    
    // Open in new tab/window
    window.open(gmailUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
}

// ========================================
// ORDER SEARCH FUNCTIONALITY (ALWAYS AVAILABLE)
// ========================================

// Function to search orders
function searchOrders() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const searchTerm = searchInput.value.trim();
    
    if (!searchTerm) {
        alert('Proszę wprowadzić tekst do wyszukania.');
        return;
    }
    
    // Show loading state
    const originalText = searchBtn.textContent;
    searchBtn.textContent = 'Szukanie...';
    searchBtn.disabled = true;
    
    // Make API request
    fetch('../public/search_orders.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `search_term=${encodeURIComponent(searchTerm)}`
    })
    .then(response => {
        return response.text().then(text => {
            console.log('Raw server response:', text);
            console.log('Response status:', response.status);
            console.log('Response headers:', [...response.headers.entries()]);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error. Raw response:', text);
                throw new Error('Server returned invalid JSON: ' + text.substring(0, 200));
            }
        });
    })
    .then(data => {
        if (data.success) {
            handleSearchResults(data.results, searchTerm);
        } else {
            throw new Error(data.error || 'Search failed');
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        alert('Błąd podczas wyszukiwania: ' + error.message);
    })
    .finally(() => {
        searchBtn.textContent = originalText;
        searchBtn.disabled = false;
    });
}

// Function to handle search results
function handleSearchResults(results, searchTerm) {
    if (results.length === 0) {
        alert(`Nie znaleziono wyników dla: "${searchTerm}"`);
        return;
    }
    
    // Group results by unique combination of zl_Numer and iai_zamowienie
    const uniqueResults = [];
    const seen = new Set();
    
    results.forEach(result => {
        const key = `${result.zl_Numer}|${result.iai_zamowienie || ''}`;
        if (!seen.has(key)) {
            seen.add(key);
            uniqueResults.push(result);
        }
    });
    
    if (uniqueResults.length === 1) {
        // Single unique result - show direct popup
        const result = uniqueResults[0];
        showOrderResult(result.zl_Numer, result.iai_zamowienie);
    } else {
        // Multiple unique results - show selection popup
        showOrderSelection(uniqueResults);
    }
}

// Function to show order selection popup when multiple results found
function showOrderSelection(results) {
    let html = '<div class="order-selection-popup">';
    html += '<h3>Znaleziono wiele wyników - wybierz numer zlecenia:</h3>';
    html += '<div class="order-list">';
    
    results.forEach((result, index) => {
        html += `<button class="order-option" onclick="selectOrder('${result.zl_Numer}', '${result.iai_zamowienie || ''}')">`;
        html += `<strong>Zlecenie:</strong> ${result.zl_Numer}<br>`;
        html += `<strong>IAI zamówienie:</strong> ${result.iai_zamowienie || 'Brak'}`;
        html += '</button>';
    });
    
    html += '</div>';
    html += '<button class="close-popup" onclick="closeOrderPopup()">Zamknij</button>';
    html += '</div>';
    
    // Create and show popup
    const popup = document.createElement('div');
    popup.id = 'orderSelectionPopup';
    popup.className = 'popup-overlay';
    popup.innerHTML = html;
    document.body.appendChild(popup);
}

// Function to select specific order from list
function selectOrder(zlNumer, iaiZamowienie) {
    closeOrderPopup();
    showOrderResult(zlNumer, iaiZamowienie);
}

// Function to show final order result
function showOrderResult(zlNumer, iaiZamowienie) {
    if (!iaiZamowienie) {
        let message = `Znaleziono zamówienie:\n\n`;
        message += `Numer zlecenia: ${zlNumer}\n`;
        message += `IAI zamówienie: Brak\n\n`;
        message += `Nie można automatycznie załadować szczegółów zamówienia bez numeru IAI.`;
        alert(message);
        return;
    }
    
    // Show confirmation before loading order details
    const message = `Znaleziono zamówienie:\n\n` +
                   `Numer zlecenia: ${zlNumer}\n` +
                   `IAI zamówienie: ${iaiZamowienie}\n\n` +
                   `Czy chcesz załadować szczegóły tego zamówienia?`;
    
    if (confirm(message)) {
        // Load order details by submitting the main search form programmatically
        loadOrderDetails(iaiZamowienie, zlNumer);
    }
}

// Function to load order details using the main application logic
function loadOrderDetails(iaiZamowienie, zlNumer) {
    // Create a form and submit it to load the order details
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php';
    form.style.display = 'none';
    
    // Add order parameter (IAI zamówienie)
    const orderInput = document.createElement('input');
    orderInput.type = 'hidden';
    orderInput.name = 'order';
    orderInput.value = iaiZamowienie;
    form.appendChild(orderInput);
    
    // Add wfmag parameter (zl_Numer)
    const wfmagInput = document.createElement('input');
    wfmagInput.type = 'hidden';
    wfmagInput.name = 'wfmag';
    wfmagInput.value = zlNumer;
    form.appendChild(wfmagInput);
    
    // Add form to document and submit
    document.body.appendChild(form);
    form.submit();
}

// Function to close order popup
function closeOrderPopup() {
    const popup = document.getElementById('orderSelectionPopup');
    if (popup) {
        document.body.removeChild(popup);
    }
}

// Add Enter key support for search input - ALWAYS AVAILABLE
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchOrders();
            }
        });
    }
});

// Auto-load courier parameters when page loads
<?php if ($order): ?>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, auto-loading courier parameters...');
    loadCourierParameters(<?= $h($order['orderSerialNumber']) ?>, '<?= $h($wfmagOrder ?? '') ?>');
    
    // Add event listener to checkbox to update button text
    const chooseSaveLocationCheckbox = document.getElementById('chooseSaveLocation');
    const generateDownloadBtn = document.getElementById('generateDownloadBtn');
    
    if (chooseSaveLocationCheckbox && generateDownloadBtn) {
        chooseSaveLocationCheckbox.addEventListener('change', function() {
            if (this.checked) {
                generateDownloadBtn.textContent = 'Generuj i pobierz';
            } else {
                generateDownloadBtn.textContent = 'Generuj i zapisz';
            }
        });
    }
});

<?php endif; ?>

</script>

<script>
// ========================================
// GLOBAL SEARCH FUNCTIONALITY (ALWAYS AVAILABLE)
// ========================================

// Search functionality is now defined above, outside conditional blocks

</script>