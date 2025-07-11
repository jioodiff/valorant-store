<?php
session_start();
$transaksiFile = __DIR__ . '/transaksi.json';
$data = file_exists($transaksiFile) ? json_decode(file_get_contents($transaksiFile), true) : [];

// Fungsi cari order_id jika ada parameter GET
if (isset($_GET['order_id'])) {
    header('Content-Type: text/html; charset=utf-8');
    $order_id = $_GET['order_id'];
    $found = false;

    foreach ($data as $t) {
        if (strtolower($t['order_id']) === strtolower($order_id)) {
            echo "<div><strong>Order ID:</strong> {$t['order_id']}</div>";
            echo "<div><strong>Riot ID:</strong> {$t['riot_id']}</div>";
            echo "<div><strong>Email:</strong> {$t['email']}</div>";
            echo "<div><strong>Item:</strong> {$t['item']}</div>";
            echo "<div><strong>Jumlah:</strong> {$t['jumlah']}</div>";
            echo "<div><strong>Metode:</strong> " . ($t['metode'] ?? '-') . "</div>";
            echo "<div><strong>Total:</strong> Rp" . number_format($t['total'] ?? 0, 0, ',', '.') . "</div>";
            echo "<div><strong>Tanggal:</strong> " . ($t['tanggal'] ?? '-') . "</div>";
            echo "<div><strong>Status:</strong> " . ($t['status'] ?? '-') . "</div>";
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo "<div style='color:red;'>Order ID tidak ditemukan.</div>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Riwayat Transaksi</title>
  <style> 
    .full-bg-wrapper {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 1000px;
      overflow: hidden;
      z-index: -1;
    }

    .bg-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      filter: brightness(0.6);
    }

    .bg-overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, rgba(0,0,0,0.9), rgba(0,0,0,1));
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      background: #000;
      color: white;
      transition: opacity 0.5s ease-in-out;
      opacity: 0;
    }

    body.loaded { opacity: 1; }
    .container {
      max-width: 1715px;
      margin: 30px auto;
      padding: 20px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 10px;
    }

    h1 { text-align: center; color: #fff; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      padding: 10px;
      border: 1px solid #444;
      text-align: left;
    }

    th { background-color: #222; }
    tr:hover { background-color: rgba(255, 255, 255, 0.05); }
    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 20px;
      background: rgba(0, 0, 0, 0);
    }

    .top-right { display: flex; gap: 10px; }
    .auth-btn {
      padding: 8px 16px;
      background:rgb(255, 0, 0); /* hijau */
      border: none;
      color: white;
      border-radius: 6px;
      cursor: pointer;
    }

    .auth-btn:hover {
      background: rgba(0, 0, 0, 0);
    }
    
    .close-btn {
      background: rgb(255, 0, 0);
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      margin-top: 10px;
    }

    .close-btn:hover {
      background: rgba(0,0,0,0);
    }

    #searchModal {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.8);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 10;
    }
    .modal-content {
      background: #111;
      padding: 30px;
      border-radius: 10px;
      text-align: center;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 0 20px rgba(255, 255, 255, 0.5); 
    }

    .modal-content input {
      width: 80%;
      padding: 10px;
      margin-top: 10px;
      margin-bottom: 20px;
      border: 1px solid #555;
      border-radius: 5px;
      background: #222;
      color: white;
    }
  </style>
</head>
<body onload="document.body.classList.add('loaded')">
  <div class="full-bg-wrapper">
    <img src="jett.jpg" class="bg-image" alt="bg valorant" />
    <div class="bg-overlay"></div>
  </div>

  <div class="top-bar">
    <div id="userEmailDisplay"></div>
    <div class="top-right">
      <button id="cariTransaksiBtn" class="auth-btn">Cari Transaksi</button>
      <button id="backBtn" class="auth-btn">Kembali ke Topup</button>
      <button id="authButton" class="auth-btn">Logout</button>
    </div>
  </div>

  <div class="container">
    <h1>Riwayat Transaksi Anda</h1>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Order ID</th>
          <th>Riot ID</th>
          <th>Email</th>
          <th>Item</th>
          <th>Jumlah</th>
          <th>Metode</th>
          <th>Total</th>
          <th>Tanggal</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="transaksiBody"></tbody>
    </table>
  </div>

  <!-- Modal -->
  <div id="searchModal">
    <div class="modal-content">
      <h3>Cari Transaksi Berdasarkan Order ID</h3>
      <input type="text" id="orderIdInput" placeholder="Masukkan Order ID" />
      <div>
        <button onclick="cariOrder()" class="auth-btn">Cari</button>
        <button onclick="tutupModal()" class="close-btn">Tutup</button>
      </div>
      <div id="hasilCari" style="margin-top:20px;text-align:left;"></div>
    </div>
  </div>

  <script>
    const userEmail = localStorage.getItem("userEmail");
    const transaksiBody = document.getElementById("transaksiBody");
    const emailDisplay = document.getElementById("userEmailDisplay");

    if (!userEmail) {
      alert("Silakan login terlebih dahulu.");
      document.body.classList.remove("loaded");
      setTimeout(() => window.location.href = "login.php", 300);
    }

    emailDisplay.innerText = "👤 " + userEmail;

    document.getElementById("authButton").onclick = () => {
      if (confirm("Yakin ingin logout?")) {
        localStorage.clear();
        document.body.classList.remove("loaded");
        setTimeout(() => window.location.href = "login.php", 300);
      }
    };

    document.getElementById("backBtn").onclick = () => {
      document.body.classList.remove("loaded");
      setTimeout(() => window.location.href = "index.php", 300);
    };

    fetch('transaksi.json')
      .then(res => res.json())
      .then(data => {
        const userData = data.filter(t => t.email === userEmail);
        if (userData.length === 0) {
          transaksiBody.innerHTML = '<tr><td colspan="10" style="text-align:center;">Belum ada transaksi.</td></tr>';
          return;
        }

        transaksiBody.innerHTML = userData.map((t, i) => `
          <tr>
            <td>${i + 1}</td>
            <td>${t.order_id || '-'}</td>
            <td>${t.riot_id}</td>
            <td>${t.email}</td>
            <td>${t.item}</td>
            <td>${t.jumlah}</td>
            <td>${t.metode || '-'}</td>
            <td>Rp${(t.total || 0).toLocaleString('id-ID')}</td>
            <td>${t.tanggal || '-'}</td>
            <td>${t.status || '-'}</td>
          </tr>
        `).join('');
      });

    document.getElementById("cariTransaksiBtn").onclick = () => {
      document.getElementById("searchModal").style.display = "flex";
    };

    function tutupModal() {
      document.getElementById("searchModal").style.display = "none";
      document.getElementById("hasilCari").innerHTML = "";
      document.getElementById("orderIdInput").value = "";
    }

    function cariOrder() {
      const orderId = document.getElementById("orderIdInput").value.trim();
      if (!orderId) return;
      fetch(`riwayat.php?order_id=${encodeURIComponent(orderId)}`)
        .then(res => res.text())
        .then(html => {
          document.getElementById("hasilCari").innerHTML = html;
        });
    }
  </script>
</body>
</html>
