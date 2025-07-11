<?php
// notification.php

$serverKey = 'Mid-server-4EJWCckaJFyT0uY9-g1nLX7w'; // Ganti sesuai server key kamu

// Baca notifikasi dari Midtrans
$rawNotif = file_get_contents('php://input');
$notif = json_decode($rawNotif, true);

// Validasi signature key (opsional tapi disarankan)
$signatureKey = $notif['signature_key'];
$orderId = $notif['order_id'];
$statusCode = $notif['status_code'];
$grossAmount = $notif['gross_amount'];
$expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

if ($signatureKey !== $expectedSignature) {
  http_response_code(403);
  exit('Invalid signature');
}

// Ambil file transaksi
$transaksiFile = __DIR__ . '/transaksi.json';
$transaksi = file_exists($transaksiFile) ? json_decode(file_get_contents($transaksiFile), true) : [];

// Update status berdasarkan order_id
foreach ($transaksi as &$t) {
  if ($t['order_id'] === $orderId) {
    $t['status'] = $notif['transaction_status']; // 'settlement', 'cancel', 'expire', etc.
    break;
  }
}

// Simpan ulang
file_put_contents($transaksiFile, json_encode($transaksi, JSON_PRETTY_PRINT));
http_response_code(200);
echo 'OK';
