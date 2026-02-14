#!/usr/bin/env php
<?php

/**
 * Test script for Outstanding Invoices API
 * Usage: php test-api.php <base_url> <token>
 */
$baseUrl = $argv[1] ?? 'http://localhost:8000';
$token = $argv[2] ?? '';

if (! $token) {
    echo "Error: Token is required\n";
    echo "Usage: php test-api.php <base_url> <token>\n";
    exit(1);
}

$tests = [
    [
        'name' => 'Get all outstanding invoices',
        'endpoint' => '/api/v1/invoices/outstanding',
        'params' => [],
    ],
    [
        'name' => 'Filter by client_id=1',
        'endpoint' => '/api/v1/invoices/outstanding',
        'params' => ['client_id' => 1],
    ],
    [
        'name' => 'Filter by campaign_id=1',
        'endpoint' => '/api/v1/invoices/outstanding',
        'params' => ['campaign_id' => 1],
    ],
    [
        'name' => 'Filter by date=2025-01-01',
        'endpoint' => '/api/v1/invoices/outstanding',
        'params' => ['date' => '2025-01-01'],
    ],
    [
        'name' => 'Filter by date range=2025-01-01,2025-12-31',
        'endpoint' => '/api/v1/invoices/outstanding',
        'params' => ['date' => '2025-01-01,2025-12-31'],
    ],
];

echo "========================================\n";
echo "Outstanding Invoices API Test\n";
echo "========================================\n";
echo "Base URL: $baseUrl\n";
echo 'Token: '.substr($token, 0, 20)."...\n";
echo "========================================\n\n";

foreach ($tests as $test) {
    echo "Test: {$test['name']}\n";
    echo str_repeat('-', 50)."\n";

    $url = $baseUrl.$test['endpoint'];
    if (! empty($test['params'])) {
        $url .= '?'.http_build_query($test['params']);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        'Accept: application/json',
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "cURL Error: $error\n";
    } else {
        echo "HTTP Status: $httpCode\n";
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            if ($data) {
                echo 'Success: '.($data['success'] ? 'true' : 'false')."\n";
                echo 'Count: '.($data['count'] ?? 0)."\n";
                echo 'Message: '.($data['message'] ?? '')."\n";
                if (! empty($data['data'])) {
                    echo "First Invoice (sample):\n";
                    $first = $data['data'][0];
                    echo "  - Number: {$first['number']}\n";
                    echo "  - Status: {$first['status']}\n";
                    echo "  - Client: {$first['client_name']}\n";
                    echo "  - Balance: {$first['balance_pending']}\n";
                }
            }
        } else {
            echo 'Response: '.substr($response, 0, 200)."\n";
        }
    }

    echo "\n";
}

echo "========================================\n";
echo "Tests Completed\n";
echo "========================================\n";
