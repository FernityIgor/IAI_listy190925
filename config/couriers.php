<?php

return [
    'changeable_couriers' => [
        // Format: 'courier_id' => 'courier_name'
        6 => 'DPD',
        26 => 'K-EX',
        100335 => 'Poczta Polska Poczex'  ,
        100583 => 'Ambro',
        // Special courier group - Kurier 1 and its alternatives
        690000 => 'Kurier 1',
        100167 => 'Balíkovna (Czeska poczta) Home delivery',
        690001 => 'Kurier 2 = ONE by Allegro Home Delivery',
        690002 => 'FOFR (przesyłi gabarytowe)'
        // Add more couriers as needed
    ],
    
    'special_courier_groups' => [
        // KEX special courier (can be changed but not selected again)
        'kex' => [
            'special_courier' => 26, // K-EX
            'alternatives' => [6, 100335, 100583] // All other changeable couriers except KEX
        ],
        // Kurier 1 special group (must be changed to specific alternatives)
        'kurier1' => [
            'special_courier' => 690000, // Kurier 1
            'alternatives' => [100167, 690001, 690002] // Specific alternatives for Kurier 1
        ]
    ]
];