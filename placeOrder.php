<?php
// placeOrder.php

header('Content-Type: application/json');

$serverKey = 'Mid-server-4EJWCckaJFyT0uY9-g1nLX7w';
$isProduction = false;

$midtransUrl = $isProduction
    ? 'https://app.midtrans.com/snap/v1/transactions'
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

$input = json_decode(file_get_contents('php://input'), true);
$orderId = uniqid('order-');

// Simpan sementara ke transaksi.json
$transaksiFile = __DIR__ . '/transaksi.json';
$transaksi = file_exists($transaksiFile) ? json_decode(file_get_contents($transaksiFile), true) : [];

$transaksi[] = [
    'order_id' => $orderId,
    'riot_id' => $input['riot_id'],
    'email' => $input['email'],
    'item' => $input['item'],
    'jumlah' => $input['qty'],
    'metode' => 'Midtrans',
    'total' => $input['total'],
    'tanggal' => date('Y-m-d'),
    'status' => 'Pending'
];

file_put_contents($transaksiFile, json_encode($transaksi, JSON_PRETTY_PRINT));

// Kirim ke Midtrans
$payload = [
    'transaction_details' => [
        'order_id' => $orderId,
        'gross_amount' => $input['total']
    ],
    'customer_details' => [
        'first_name' => $input['riot_id'],
        'email' => $input['email']
    ],
    'item_details' => [[
        'id' => 'vp-' . rand(1000, 9999),
        'price' => $input['price'],
        'quantity' => $input['qty'],
        'name' => $input['item']
    ]]
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $midtransUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($serverKey . ':')
    ]
]);

$response = curl_exec($curl);
curl_close($curl);
echo $response;
