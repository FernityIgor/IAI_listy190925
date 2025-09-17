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
        
        // Create the API client with config values
        $this->apiClient = new OrderApiClient(
            $this->config['api']['url'],
            $this->config['api']['key']
        );
    }

    public function show()
    {
        // --- Action Handling ---
        // Check if the "Add Package" form was submitted
        if (isset($_POST['add_package'])) {
            $orderId = (int)$_POST['order_id'];
            $courierId = (int)$_POST['courier_id'];
            $returnOrder = $_POST['return_order'] ?? '';
            
            if ($orderId > 0 && $courierId > 0) {
                $result = $this->apiClient->addPackage($orderId, $courierId);
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
        $orderName = trim($_GET['order'] ?? $_POST['order'] ?? '');
        $orderData = null;
        $packagesData = null;
        $error = '';

        if (!empty($orderName)) {
            $orderData = $this->apiClient->fetchOrder((int)$orderName);
            if ($orderData === null) {
                $error = "Failed to fetch order data from the API.";
            } elseif (empty($orderData['Results'])) {
                $error = "Order not found.";
            } else {
                // If order is found, also fetch package data
                $packagesData = $this->apiClient->fetchPackages((int)$orderName);
            }
        }

        // Prepare variables for the view
        $order = null;
        $clientResult = null;
        $products = [];
        $packages = [];
        $courierId = null; // Initialize courierId
        if ($orderData && isset($orderData['Results'][0])) {
            $order = $orderData['Results'][0];
            $clientResult = $order['clientResult'];
            $products = $order['orderDetails']['productsResults'];
            // Extract the courierId from the dispatch details
            $courierId = $order['orderDetails']['dispatch']['courierId'] ?? null;
        }
        if ($packagesData && isset($packagesData['results'])) {
            $packages = $packagesData['results'];
        }

        // Add this before view rendering
        $changeableCouriers = $this->couriers['changeable_couriers'];

        // --- View Rendering ---
        $pageTitle = 'Order Details';
        $shopNames = $this->config['shops'];
        $h = function($str) {
            return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
        };

        require_once __DIR__ . '/../../views/layout.php';
    }
}