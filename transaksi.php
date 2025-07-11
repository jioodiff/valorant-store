<?php
session_start();
$transaksiFile = __DIR__ . '/transaksi.json'; // ✅ gunakan __DIR__
$data = file_exists($transaksiFile) ? json_decode(file_get_contents($transaksiFile), true) : [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Panel - Transaksi</title>
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
      background-color: #000;
      color: white;
      margin: 0;
      padding: 20px;
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
    }
    body.loaded { opacity: 1; }
    .container {
      max-width: 1715px;
      margin: auto;
      background: rgba(255, 255, 255, 0.05);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(255, 255, 255, 0.1);
    }
    h1 { text-align: center; color: white; }
    input, select {
      padding: 10px;
      width: 98.6%;
      margin: 10px 0;
      border: 1px solid #444;
      border-radius: 6px;
      background: rgba(255, 255, 255, 0.1);
      color: white;
    }
    button {
      padding: 10px 20px;
      background-color:rgb(255, 0, 17);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 10px;
    }
    button:hover { background-color:#444; }
    table {
      width: 100%;
      margin-top: 15px;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #555;
      padding: 10px;
    }
    th { background: rgba(255, 255, 255, 0.1); }
    tr:hover { background-color: rgba(255, 255, 255, 0.05); cursor: pointer; }
    .form-buttons button { margin-right: 10px; }
    .action-buttons {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 25px;
    }
  </style>
</head>
<body onload="document.body.classList.add('loaded')">
<div class="full-bg-wrapper">
  <img src="jett.jpg" class="bg-image" alt="bg valorant" />
  <div class="bg-overlay"></div>
</div>
<div class="container">
  <h1>Daftar Transaksi</h1>

  <table>
    <thead>
      <tr>
        <th>ID</th>
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
    <tbody id="dataBody">
      <?php foreach ($data as $d): ?>
        <tr data-id="<?= $d['order_id'] ?>" onclick="editFromPHP(this)">
          <td><?= $d['order_id'] ?></td>
          <td><?= $d['riot_id'] ?></td>
          <td><?= $d['email'] ?></td>
          <td><?= $d['item'] ?></td>
          <td><?= $d['jumlah'] ?></td>
          <td><?= $d['metode'] ?></td>
          <td>Rp<?= number_format($d['total'], 0, ',', '.') ?></td>
          <td><?= $d['tanggal'] ?></td>
          <td><?= $d['status'] ?></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>

  <form id="crudForm" style="display:none;">
    <input type="hidden" id="recordId" />
    <label>Riot ID:</label>
    <input type="text" id="riotIdInput" required />
    <label>Email:</label>
    <input type="email" id="emailInput" />
    <label>Item:</label>
    <input type="text" id="itemInput" required />
    <label>Jumlah:</label>
    <input type="number" id="jumlahInput" required />
    <label>Metode:</label>
    <input type="text" id="metodeInput" required />
    <label>Total Harga:</label>
    <input type="number" id="totalInput" required />
    <label>Tanggal:</label>
    <input type="date" id="tanggalInput" required />
    <label>Status:</label>
    <select id="statusInput" required>
      <option value="Pending">Pending</option>
      <option value="Paid">Paid</option>
      <option value="Cancelled">Cancelled</option>
    </select>
    <div class="form-buttons">
      <button type="submit">Simpan</button>
      <button type="button" onclick="deleteData()">Hapus</button>
      <button type="button" onclick="hideForm()">Batal</button>
    </div>
  </form>

  <div class="action-buttons">
    <button id="addBtn">Edit Transaksi</button>
    <button id="logoutBtn">Logout</button>
  </div>
</div>

<script>
  const recordId = document.getElementById("recordId")
  const riotIdInput = document.getElementById("riotIdInput")
  const emailInput = document.getElementById("emailInput")
  const itemInput = document.getElementById("itemInput")
  const jumlahInput = document.getElementById("jumlahInput")
  const metodeInput = document.getElementById("metodeInput")
  const totalInput = document.getElementById("totalInput")
  const tanggalInput = document.getElementById("tanggalInput")
  const statusInput = document.getElementById("statusInput")
  const crudForm = document.getElementById("crudForm")

  const userEmail = localStorage.getItem("userEmail")
  if (userEmail !== "admin@gmail.com") {
    alert("❌ Anda bukan admin. Akses ditolak.")
    document.body.classList.remove("loaded")
    setTimeout(() => window.location.href = "login.php", 300)
  }

  document.getElementById("addBtn").onclick = () => showForm()
  document.getElementById("logoutBtn").onclick = () => {
    localStorage.clear()
    document.body.classList.remove("loaded")
    setTimeout(() => window.location.href = "login.php", 300)
  }

  function showForm(data = null) {
    crudForm.style.display = "block"
    if (data) {
      recordId.value = data.id
      riotIdInput.value = data.riot_id
      emailInput.value = data.email
      itemInput.value = data.item
      jumlahInput.value = data.jumlah
      metodeInput.value = data.metode
      totalInput.value = data.total
      tanggalInput.value = data.tanggal
      statusInput.value = data.status
    } else {
      recordId.value = ""
      riotIdInput.value = ""
      emailInput.value = ""
      itemInput.value = ""
      jumlahInput.value = 1
      metodeInput.value = ""
      totalInput.value = 0
      tanggalInput.value = new Date().toISOString().split("T")[0]
      statusInput.value = "Pending"
    }
  }

  function editFromPHP(row) {
    const cells = row.getElementsByTagName("td")
    const id = row.getAttribute("data-id")
    showForm({
      id: id,
      riot_id: cells[1].textContent,
      email: cells[2].textContent,
      item: cells[3].textContent,
      jumlah: cells[4].textContent,
      metode: cells[5].textContent,
      total: cells[6].textContent.replace("Rp", "").replace(/\./g, ""),
      tanggal: cells[7].textContent,
      status: cells[8].textContent
    })
  }

  crudForm.onsubmit = function(e) {
    e.preventDefault()
    fetch("transaksi.json")
      .then(res => res.json())
      .then(transaksi => {
        transaksi = transaksi || []
        const id = recordId.value || Date.now().toString()
        const data = {
          order_id: id,
          riot_id: riotIdInput.value,
          email: emailInput.value || "-",
          item: itemInput.value,
          jumlah: parseInt(jumlahInput.value),
          metode: metodeInput.value,
          total: parseInt(totalInput.value),
          tanggal: tanggalInput.value,
          status: statusInput.value
        }

        if (recordId.value) {
          transaksi = transaksi.map(t => t.order_id == id ? data : t)
        } else {
          transaksi.push(data)
        }

        fetch("save_transaksi.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(transaksi)
        }).then(() => {
          hideForm()
          location.reload()
        })
      })
  }

  window.deleteData = function () {
    if (!confirm("Yakin ingin menghapus transaksi ini?")) return
    const id = recordId.value
    fetch("transaksi.json")
      .then(res => res.json())
      .then(transaksi => {
        transaksi = transaksi.filter(t => t.order_id != id)
        fetch("save_transaksi.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(transaksi)
        }).then(() => {
          hideForm()
          location.reload()
        })
      })
  }

  window.hideForm = function () {
    crudForm.reset()
    crudForm.style.display = "none"
  }
</script>
</body>
</html>