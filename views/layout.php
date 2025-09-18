<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $h($pageTitle) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php 
        // Include the specific view for this page
        require_once __DIR__ . '/order_view.php'; 
    ?>
    
    <!-- Modal (Should be hidden by default) -->
    <div class="modal-overlay" id="deleteModal" style="display: none;">
        <div class="modal-content">
            <p>Usunąć paczkę można tylko w panelu IDoSell.</p>
            <p>Czy przejść do panelu?</p>
            <div class="modal-buttons">
                <button class="modal-button confirm" id="confirmDelete">Tak</button>
                <button class="modal-button cancel" id="cancelDelete">Nie</button>
            </div>
        </div>
    </div>

    <!-- Weight Modal -->
    <div id="weightModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <h3>Zmień wagę paczki</h3>
            <form method="POST" action="index.php">
                <input type="hidden" name="update_weight" value="1">
                <input type="hidden" id="weightOrderId" name="order_id">
                <input type="hidden" id="weightPackageId" name="package_id">
                <input type="hidden" id="weightCourierId" name="courier_id">
                
                <div class="form-group">
                    <label for="weightInput">Waga (g):</label>
                    <div class="weight-presets">
                        <button type="button" class="preset-btn" onclick="setWeight(1500)">1.5 kg</button>
                        <button type="button" class="preset-btn" onclick="setWeight(7000)">7 kg</button>
                        <button type="button" class="preset-btn" onclick="setWeight(28000)">28 kg</button>
                        <button type="button" class="preset-btn" onclick="setWeight(31500)">31.5 kg</button>
                    </div>
                    <input type="number" id="weightInput" name="weight" min="1" required>
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" class="modal-button confirm">Zapisz</button>
                    <button type="button" onclick="closeWeightModal()" class="modal-button cancel">Anuluj</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Courier Modal -->
    <div class="courier-modal modal-overlay" id="courierModal" style="display: none;">
        <div class="modal-content">
            <h3>Wybierz kuriera</h3>
            <div class="courier-list">
                <?php foreach ($changeableCouriers as $id => $name): ?>
                    <button 
                        type="button"
                        onclick="selectCourier(<?= $h($id) ?>)" 
                        class="courier-button">
                        <?= $h($name) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <div class="modal-buttons">
                <button onclick="closeCourierModal()" class="modal-button cancel">Anuluj</button>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(orderNumber) {
            const modal = document.getElementById('deleteModal');
            const confirm = document.getElementById('confirmDelete');
            const cancel = document.getElementById('cancelDelete');

            modal.style.display = 'flex';

            confirm.onclick = function() {
                window.open('https://dkwadrat.pl/panel/orderd.php?idt=' + orderNumber + '#tr_packagesSectionRow', '_blank');
                modal.style.display = 'none';
            };

            cancel.onclick = function() {
                modal.style.display = 'none';
            };

            // Close modal if clicking outside
            modal.onclick = function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            };
        }

        let currentOrderId = null;
        let currentPackageId = null;
        let currentCourierId = null;

        function showWeightInput(orderId, packageId, courierId) {
            console.log('showWeightInput called with:', orderId, packageId, courierId); // Add this debug line
            currentOrderId = orderId;
            currentPackageId = packageId;
            currentCourierId = courierId;
            
            document.getElementById('weightModal').style.display = 'flex';
            document.getElementById('weightInput').focus();
        }

        function closeWeightModal() {
            document.getElementById('weightModal').style.display = 'none';
            document.getElementById('weightInput').value = '';
        }

        // Add ESC key support to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('weightModal').style.display = 'none';
                document.getElementById('deleteModal').style.display = 'none';
                document.getElementById('courierModal').style.display = 'none';
            }
        });

        // Add click outside modal to close
        document.getElementById('weightModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeWeightModal();
            }
        });

        function setWeight(weight) {
            document.getElementById('weightInput').value = weight;
        }

        function saveWeight() {
            const weight = parseInt(document.getElementById('weightInput').value);
            if (!weight || weight < 100) {
                alert('Proszę podać prawidłową wagę (minimum 100g)');
                return;
            }

            // Send the update request
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `update_weight=1&order_id=${currentOrderId}&package_id=${currentPackageId}&courier_id=${currentCourierId}&weight=${weight}`
            })
            .then(response => {
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Wystąpił błąd podczas aktualizacji wagi.');
            });
        }

        let selectedOrderId = null;

        function showCourierSelect(orderId, currentCourierId) {
            console.log('Opening courier select for order:', orderId); // Debug line
            selectedOrderId = orderId;
            document.getElementById('courierModal').style.display = 'flex';
        }

        function closeCourierModal() {
            document.getElementById('courierModal').style.display = 'none';
        }

        function selectCourier(courierId) {
            // Find and disable all courier buttons
            const courierList = document.querySelector('.courier-list');
            const selectedButton = document.querySelector(`button[onclick*="selectCourier(${courierId})"]`);
            
            // Add loading state
            courierList.classList.add('disabled');
            selectedButton.classList.add('loading');
            
            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `update_courier=1&order_id=${selectedOrderId}&courier_id=${courierId}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Wystąpił błąd podczas zmiany kuriera.');
                // Remove loading state if there's an error
                courierList.classList.remove('disabled');
                selectedButton.classList.remove('loading');
            });
        }

        // Add this to handle Enter key in the input
        document.getElementById('weightInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                saveWeight();
            }
        });

        function showWeightModal(orderId, packageId, courierId, currentWeight) {
            const modal = document.getElementById('weightModal');
            document.getElementById('weightOrderId').value = orderId;
            document.getElementById('weightPackageId').value = packageId;
            document.getElementById('weightCourierId').value = courierId;
            document.getElementById('weightInput').value = currentWeight;
            modal.style.display = 'flex';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Close modal when clicking outside
            document.getElementById('weightModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeWeightModal();
                }
            });
        });
    </script>
</body>
</html>