<?php
// midtrans-notif.php

$notif = json_decode(file_get_contents("php://input"), true);
$orderId = $notif['order_id'] ?? '';
$status = $notif['transaction_status'] ?? '';

$transaksiFile = __DIR__ . '/transaksi.json';
$transaksi = file_exists($transaksiFile) ? json_decode(file_get_contents($transaksiFile), true) : [];

foreach ($transaksi as &$t) {
  if (isset($t['order_id']) && $t['order_id'] === $orderId) {
    $t['status'] = ucfirst($status);
    break;
  }
}

file_put_contents($transaksiFile, json_encode($transaksi, JSON_PRETTY_PRINT));
http_response_code(200);
