<?php
$transaksiFile = __DIR__ . '/transaksi.json'; // ✅ gunakan __DIR__
$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid data"]);
  exit;
}

file_put_contents($transaksiFile, json_encode($data, JSON_PRETTY_PRINT));
echo json_encode(["success" => true]);
?>
