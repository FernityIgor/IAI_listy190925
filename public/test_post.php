<?php
error_log('TEST POST received: ' . print_r($_POST, true));
header('Content-Type: application/json');
echo json_encode(['test' => 'success', 'received' => $_POST]);
?>