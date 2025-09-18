<?php

namespace App\Api;

class OrderApiClient
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct(string $apiUrl, string $apiKey)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    public function fetchOrder(int $orderSerialNumber): ?array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'params' => [
                    'ordersSerialNumbers' => [$orderSerialNumber]
                ]
            ]),
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: " . $this->apiKey,
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            // In a real app, you would log this error instead of just returning null
            error_log("cURL Error: " . $err);
            return null;
        }

        return json_decode($response, true);
    }

    public function fetchPackages(int $orderSerialNumber): ?array
    {
        $url = "https://dkwadrat.pl/api/admin/v6/orders/packages?orderNumbers=" . $orderSerialNumber;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: " . $this->apiKey,
                "accept: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("cURL Error (Packages): " . $err);
            return null;
        }

        return json_decode($response, true);
    }

    public function updatePackageWeight(int $orderId, int $packageId, int $courierId, int $weight = 1000): ?array
    {
        $url = "https://dkwadrat.pl/api/admin/v6/orders/packages";
        $payload = [
            'params' => [
                'orderPackages' => [
                    [
                        'packages' => [
                            [
                                'deliveryPackageParameters' => [
                                    'productWeight' => $weight
                                ],
                                'courierId' => (string)$courierId,
                                'deliveryPackageId' => $packageId
                            ]
                        ],
                        'eventId' => (string)$orderId,
                        'eventType' => 'order'
                    ]
                ]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: " . $this->apiKey,
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("cURL Error (Update Weight): " . $err);
            return null;
        }

        return json_decode($response, true);
    }

    public function addPackage(int $orderId, int $courierId): ?array
    {
        // First add the package
        $result = $this->addPackageBase($orderId, $courierId);
        if (!$result) {
            return null;
        }

        // Wait a moment for the package to be created
        sleep(1);

        // Fetch updated package list to get the new package ID
        $packages = $this->fetchPackages($orderId);
        if (!$packages || !isset($packages['results'])) {
            return null;
        }

        // Find packages with weight 0 and update them
        foreach ($packages['results'] as $package) {
            $weight = $package['deliveryPackage']['deliveryPackageParameters']['deliveryWeight'] ?? 0;
            $packageId = $package['deliveryPackage']['deliveryPackageId'] ?? null;
            
            if ($weight == 0 && $packageId) {
                $this->updatePackageWeight($orderId, $packageId, $courierId);
            }
        }

        return $packages;
    }

    private function addPackageBase(int $orderId, int $courierId): ?array
    {
        $url = "https://dkwadrat.pl/api/admin/v6/packages/packages";
        $payload = [
            'params' => [
                'orderPackages' => [
                    [
                        'packages' => [
                            [
                                'shippingStoreCosts' => [
                                    'tax' => 23
                                ],
                                'delivery' => $courierId
                            ]
                        ],
                        'orderId' => (string)$orderId,
                        'orderType' => 'order'
                    ]
                ]
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: " . $this->apiKey,
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("cURL Error (Add Package Base): " . $err);
            return null;
        }

        return json_decode($response, true);
    }

    public function updateCourier(int $orderNumber, int $courierId): ?array
    {
        $url = "https://dkwadrat.pl/api/admin/v6/orders/courier";
        $payload = [
            'params' => [
                'orderSerialNumber' => $orderNumber,
                'courierId' => $courierId
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: " . $this->apiKey,
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("cURL Error (Update Courier): " . $err);
            return null;
        }

        return json_decode($response, true);
    }

    public function fetchCourierProfiles(): ?array
    {
        $url = "https://dkwadrat.pl/api/admin/v6/couriers/assignedToShippingProfiles";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: " . $this->apiKey,
                "accept: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("cURL Error (Courier Profiles): " . $err);
            return null;
        }

        return json_decode($response, true);
    }

    public function generateShippingLabels(int $orderNumber, array $parameters = []): ?array
    {
        // Debug: First check what's in the actual IAI order data
        $orderData = $this->fetchOrder($orderNumber);
        error_log("=== IAI ORDER DATA DEBUG ===");
        error_log("Order Number: " . $orderNumber);
        if ($orderData) {
            error_log("IAI Order Data: " . print_r($orderData, true));
            // Look specifically for delivery address info
            if (isset($orderData['orders'][0]['deliveryAddress'])) {
                error_log("Delivery Address from IAI: " . print_r($orderData['orders'][0]['deliveryAddress'], true));
            }
            if (isset($orderData['orders'][0]['invoiceAddress'])) {
                error_log("Invoice Address from IAI: " . print_r($orderData['orders'][0]['invoiceAddress'], true));
            }
        } else {
            error_log("No order data found in IAI for order: " . $orderNumber);
        }
        
        // Debug logging
        $debugLog = "=== API GENERATE LABELS DEBUG ===\n";
        $debugLog .= "Order Number: " . $orderNumber . "\n";
        $debugLog .= "Input parameters: " . print_r($parameters, true) . "\n";
        
        // Debug: Log each parameter transformation
        $debugLog .= "\n=== PARAMETER TRANSFORMATION DEBUG ===\n";
        foreach ($parameters as $key => $value) {
            $originalValue = $value;
            $numericParams = ['service', 'carryIn', 'cud', 'duty', 'inPers', 'rod', 'selfCol', 'privpers', 'tiresService', 'dec'];
            if (in_array($key, $numericParams) && is_numeric($value)) {
                $value = (int)$value;
                $debugLog .= "Transformed {$key}: '{$originalValue}' (string) -> {$value} (int)\n";
            } else {
                $debugLog .= "Kept {$key}: '{$originalValue}' (unchanged)\n";
            }
        }
        
        $apiKey = "YXBwbGljYXRpb24xODpzSUdaNFU1ZzFwVnV2K3R4bExZU2lxRnR6dytHa0hiY3dhQ29HZ1BOdFdOSEtlekRYR0F3NkpFZEFCZGk0RWQ0";
        $endpoint = "https://dkwadrat.pl/api/admin/v6/packages/labels";

        $eventId = $orderNumber;
        $eventType = "order";
        
        // Convert form parameters to parcel parameters format if provided
        // Otherwise use empty array (fallback approach)
        $parcels = [];
        if (!empty($parameters)) {
            // Transform the form parameters into the format expected by the API
            $parcelParams = [];
            foreach ($parameters as $key => $value) {
                $debugLog .= "Processing parameter: key='{$key}', value='{$value}', type=" . gettype($value) . "\n";
                
                if (!empty($value) || $value === '0') { // Include if has value or is explicitly '0'
                    // Convert numeric strings to actual numbers for certain parameters
                    $numericParams = ['service', 'carryIn', 'cud', 'duty', 'inPers', 'rod', 'selfCol', 'privpers', 'tiresService', 'dec'];
                    if (in_array($key, $numericParams) && is_numeric($value)) {
                        $originalValue = $value;
                        $value = (int)$value;
                        $debugLog .= "Converted {$key}: '{$originalValue}' -> {$value}\n";
                    }
                    
                    // IMPORTANT: Use "id" instead of "key" for DKwadrat API
                    $parcelParams[] = [
                        'id' => $key,
                        'value' => $value
                    ];
                    
                    $debugLog .= "Added parameter: id='{$key}', value='{$value}'\n";
                } else {
                    $debugLog .= "Skipping parameter {$key} (empty value: '{$value}')\n";
                }
            }
            
            if (!empty($parcelParams)) {
                // FIXED: Use single array format for DKwadrat API
                $parcels = $parcelParams;
            }
        }

        $payload = [
            'params' => [
                'eventId' => $eventId,
                'eventType' => $eventType,
                'parcelParameters' => $parcels
            ]
        ];

        $debugLog .= "Final payload to API: " . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        // Create curl verbose log file
        $curlLogFile = __DIR__ . '/../../logs/curl_verbose.log';
        if (!file_exists(dirname($curlLogFile))) {
            mkdir(dirname($curlLogFile), 0755, true);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: {$apiKey}",
                "Accept: application/json, application/pdf",
                "Content-Type: application/json"
            ],
            // Add verbose debugging to capture full HTTP request
            CURLOPT_VERBOSE => true,
            CURLOPT_STDERR => fopen($curlLogFile, 'a+'),
        ]);

        // Log the complete HTTP request details
        $debugLog .= "\n=== HTTP REQUEST DETAILS ===\n";
        $debugLog .= "URL: " . $endpoint . "\n";
        $debugLog .= "Method: POST\n";
        $debugLog .= "Headers:\n";
        $debugLog .= "  X-API-KEY: " . $apiKey . "\n";
        $debugLog .= "  Accept: application/json, application/pdf\n";
        $debugLog .= "  Content-Type: application/json\n";
        $debugLog .= "Raw JSON Body:\n" . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n";
        $debugLog .= "Body Length: " . strlen(json_encode($payload, JSON_UNESCAPED_UNICODE)) . " bytes\n";

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseHeaders = curl_getinfo($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $debugLog .= "\n=== HTTP RESPONSE DETAILS ===\n";
        $debugLog .= "HTTP Status Code: " . $httpCode . "\n";
        $debugLog .= "Response Headers:\n";
        $debugLog .= "  Content-Type: " . ($responseHeaders['content_type'] ?? 'unknown') . "\n";
        $debugLog .= "  Content-Length: " . ($responseHeaders['download_content_length'] ?? 'unknown') . "\n";
        $debugLog .= "Total Time: " . ($responseHeaders['total_time'] ?? 'unknown') . " seconds\n";
        
        if ($err) {
            $debugLog .= "cURL Error: " . $err . "\n";
        }
        
        // Log first part of response (avoid logging huge binary data if it's PDF)
        if ($httpCode == 200 && isset($responseHeaders['content_type']) && strpos($responseHeaders['content_type'], 'application/json') !== false) {
            $debugLog .= "API Response (JSON): " . substr($response, 0, 2000) . "\n";
        } else {
            $debugLog .= "API Response: HTTP " . $httpCode . " (Content-Type: " . ($responseHeaders['content_type'] ?? 'unknown') . ")\n";
            $debugLog .= "Response preview: " . substr($response, 0, 200) . "\n";
        }
        
        $debugLog .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
        file_put_contents(__DIR__ . '/../../logs/debug_labels.log', $debugLog, FILE_APPEND);

        if ($err) {
            error_log("cURL Error (Generate Labels): " . $err);
            return null;
        }

        $responseData = json_decode($response, true);
        
        // Add HTTP status and error details to response for frontend
        $result = [
            'http_status' => $httpCode,
            'api_response' => $responseData,
            'success' => $httpCode == 200
        ];
        
        // Handle different response statuses
        if ($httpCode == 207) {
            // API returned partial success/error
            if (isset($responseData['errors'])) {
                $result['error_message'] = $responseData['errors']['faultString'] ?? 'Nieznany błąd API';
                $result['error_code'] = $responseData['errors']['faultCode'] ?? 'unknown';
            } else {
                $result['error_message'] = 'Status 207 - częściowy błąd API';
            }
        } elseif ($httpCode != 200) {
            $result['error_message'] = "HTTP {$httpCode} - Błąd serwera";
        }

        return $result;
    }

    public function downloadLabels(int $orderSerialNumber): ?array
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://dkwadrat.pl/api/admin/v6/orders/labels?orderSerialNumber={$orderSerialNumber}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: " . $this->apiKey,
                "accept: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            error_log("cURL Error (Download Labels): " . $err);
            return null;
        }

        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Not JSON - return raw response for debugging
            error_log("Non-JSON response from labels API: " . $response);
            return ['error' => 'Invalid JSON response', 'raw_response' => $response];
        }

        // Create labels directory if it doesn't exist
        $labelsDir = __DIR__ . '/../../storage/labels';
        if (!is_dir($labelsDir)) {
            mkdir($labelsDir, 0755, true);
        }

        $savedFiles = [];
        
        if (!empty($data['labels']) && is_array($data['labels'])) {
            foreach ($data['labels'] as $idx => $b64) {
                $pdf = base64_decode($b64, true);
                if ($pdf === false) {
                    error_log("Error decoding label #{$idx} for order {$orderSerialNumber}");
                    continue;
                }
                
                $filename = "label_{$orderSerialNumber}_" . ($idx + 1) . ".pdf";
                $filepath = $labelsDir . "/" . $filename;
                
                if (file_put_contents($filepath, $pdf) !== false) {
                    $savedFiles[] = [
                        'filename' => $filename,
                        'filepath' => $filepath,
                        'size' => strlen($pdf)
                    ];
                } else {
                    error_log("Failed to save label file: {$filepath}");
                }
            }
        }

        return [
            'success' => !empty($savedFiles),
            'files' => $savedFiles,
            'total_labels' => count($savedFiles),
            'api_response' => $data
        ];
    }
}