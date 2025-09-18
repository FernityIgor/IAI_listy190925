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
}