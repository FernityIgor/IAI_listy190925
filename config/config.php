<?php

return [
    'api' => [
        'url' => 'https://dkwadrat.pl/api/admin/v6/orders/orders/search',
        'key' => 'YXBwbGljYXRpb24xODpzSUdaNFU1ZzFwVnV2K3R4bExZU2lxRnR6dytHa0hiY3dhQ29HZ1BOdFdOSEtlekRYR0F3NkpFZEFCZGk0RWQ0'
    ],
    'storage' => [
        'labels_directory' => DIRECTORY_SEPARATOR === '/' ? '/tmp/listy_iai' : 'C:\listy_iai'
    ],
    'printing' => [
        'sumatra_path' => 'C:\Program Files\SumatraPDF\SumatraPDF.exe',
        'default_printer' => 'Microsoft Print to PDF'
    ],
    'shops' => [
        4 => 'furnizone.cz',
        5 => 'dwkadrat.pl',
        6 => 'b2b.fernity'
    ]
];