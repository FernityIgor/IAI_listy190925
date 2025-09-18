<?php

namespace App\Controller;

use App\Api\OrderApiClient;

class OrderController
{
    private OrderApiClient $apiClient;
    private array $config;
    private array $couriers; // Add this line

    public function __construct()
    {
        // Load configuration
        $this->config = require __DIR__ . '/../../config/config.php';
        $this->couriers = require __DIR__ . '/../../config/couriers.php';
        
        // Debug log the API configuration
        error_log("API URL: " . $this->config['api']['url']);
        error_log("API Key length: " . strlen($this->config['api']['key']));
        
        // Create the API client with config values
        $this->apiClient = new OrderApiClient(
            $this->config['api']['url'],
            $this->config['api']['key']
        );
    }

    public function show()
    {
        // Initialize error variable
        $error = '';
        $debug = [];  // Add this array to collect debug info

        // Handle AJAX request for package parameters
        if (isset($_GET['action']) && $_GET['action'] === 'get_package_params') {
            $orderNumber = (int)$_GET['order'];
            $packages = $this->apiClient->fetchPackages($orderNumber);
            
            if ($packages && isset($packages['results'][0]['deliveryPackage']['deliveryPackageParameters']['parcelParameters'])) {
                $parameters = $packages['results'][0]['deliveryPackage']['deliveryPackageParameters']['parcelParameters'];
                
                // Modify default values for boolean options
                foreach ($parameters as &$param) {
                    if (isset($param['options']) && count($param['options']) === 2) {
                        // Check if this is a yes/no parameter
                        $hasYesNo = false;
                        foreach ($param['options'] as $option) {
                            if (in_array($option['id'], ['0', '1']) || in_array($option['name'], ['tak', 'nie'])) {
                                $hasYesNo = true;
                                break;
                            }
                        }
                        
                        // If it's a yes/no parameter, set default to "no"
                        if ($hasYesNo) {
                            $param['defaultValue'] = '0';
                        }
                    }
                }
                unset($param); // Clean up reference
                
                header('Content-Type: application/json');
                echo json_encode([
                    'parameters' => $parameters
                ]);
                exit;
            }
            
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to fetch parameters']);
            exit;
        }


        // --- Action Handling ---
        // Check if the "Add Package" form was submitted
        if (isset($_POST['add_package'])) {
            $orderId = (int)$_POST['order_id'];
            $courierId = (int)$_POST['courier_id'];
            $returnOrder = $_POST['return_order'] ?? '';
            
            if ($orderId > 0 && $courierId > 0) {
                $result = $this->apiClient->addPackage($orderId, $courierId);
                error_log('Add Package Response: ' . print_r($result, true));
                
                if ($result === null) {
                    error_log('Failed to add package for order: ' . $orderId);
                }
                
                // Redirect back to the same page with the order number
                header('Location: index.php?order=' . urlencode($returnOrder));
                exit();
            }
        }

        // Check if the "Update Weight" form was submitted
        if (isset($_POST['update_weight'])) {
            $orderId = (int)$_POST['order_id'];
            $packageId = (int)$_POST['package_id'];
            $courierId = (int)$_POST['courier_id'];
            $weight = (int)$_POST['weight'];
            
            if ($orderId > 0 && $packageId > 0 && $weight > 0) {
                $this->apiClient->updatePackageWeight($orderId, $packageId, $courierId, $weight);
                header('Location: index.php?order=' . $orderId);
                exit();
            }
        }

        // Add this to handle courier updates
        if (isset($_POST['update_courier'])) {
            $orderId = (int)$_POST['order_id'];
            $courierId = (int)$_POST['courier_id'];
            
            if ($orderId > 0 && isset($this->couriers['changeable_couriers'][$courierId])) {
                $this->apiClient->updateCourier($orderId, $courierId);
                header('Location: index.php?order=' . $orderId);
                exit();
            }
        }

        // --- Data Handling ---
        $orderName = '';
        
        // Handle both POST and GET requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orderName = trim($_POST['order'] ?? '');
        } else {
            $orderName = trim($_GET['order'] ?? '');
        }

        $orderData = null;
        $packagesData = null;
        $error = '';

        // Initialize variables
        $order = null;
        $clientResult = null;
        $products = [];
        $packages = [];
        $courierId = null;

        if (!empty($orderName)) {
            // Store debug info
            $debug[] = "Attempting to fetch order: " . $orderName;
            
            $orderData = $this->apiClient->fetchOrder((int)$orderName);
            
            // Store API response for debugging
            $debug[] = "API Response: " . print_r($orderData, true);
            
            if ($orderData === null) {
                $error = "Failed to fetch order data from the API. Please check:
                         <div class='error-details'>
                         1. API Key validity
                         2. Network connection
                         3. Order number format
                         Current order number: {$orderName}
                         Debug info: " . implode("<br>", $debug) . "
                         </div>";
            } elseif (empty($orderData['Results'])) {
                error_log("OrderData has no Results");
                $error = "Order not found. Please verify the order number.
                         <div class='error-details'>
                         Searched for order: {$orderName}
                         API returned empty results.
                         </div>";
            } else {
                error_log("Successfully fetched order data");
                // If order is found, also fetch package data
                $packagesData = $this->apiClient->fetchPackages((int)$orderName);
                
                // Debug logging
                error_log('Packages API Response: ' . print_r($packagesData, true));

                if ($orderData && isset($orderData['Results'][0])) {
                    $order = $orderData['Results'][0];
                    $clientResult = $order['clientResult'];
                    $products = $order['orderDetails']['productsResults'];
                    
                    // Get courier ID from order details
                    $courierId = $order['orderDetails']['dispatch']['courierId'] ?? null;
                }
                
                if ($packagesData && isset($packagesData['results'])) {
                    $packages = $packagesData['results'];
                }
            }
        }

        // Add this before requiring the layout
        if (isset($_GET['debug']) && $_GET['debug'] === '1') {
            echo "<pre>" . print_r($debug, true) . "</pre>";
        }

        // --- View Rendering ---
        // Fetch courier profiles
        $courierProfiles = $this->apiClient->fetchCourierProfiles();
        $courierSupportsMultiplePackages = false;
        $courierExists = false;

        if ($courierProfiles && isset($courierProfiles['couriers']) && $courierId) {
            $courierExists = isset($courierProfiles['couriers'][$courierId]);
            if ($courierExists) {
                $courierSupportsMultiplePackages = 
                    $courierProfiles['couriers'][$courierId]['multiple_packages_support'] === '1';
            }
        }

        // Add these variables to be used in the view
        $viewData = [
            'pageTitle' => 'Order Details',
            'shopNames' => $this->config['shops'],
            'changeableCouriers' => $this->couriers['changeable_couriers'],
            'h' => function($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); },
            'courierExists' => $courierExists,
            'courierSupportsMultiplePackages' => $courierSupportsMultiplePackages,
            'order' => $order,
            'clientResult' => $clientResult,
            'products' => $products,
            'packages' => $packages,
            'courierId' => $courierId,
            'error' => $error
        ];

        extract($viewData);
        require_once __DIR__ . '/../../views/layout.php';
    }
}