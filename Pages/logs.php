<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RFID Scan Logs</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      padding: 40px;
    }

    h1 {
      text-align: center;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
      background-color: #fff;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    th, td {
      padding: 12px 15px;
      border: 1px solid #ccc;
      text-align: center;
    }

    th {
      background-color: #2c3e50;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .tag {
      font-weight: bold;
      font-family: monospace;
    }

    .action-normal { color: #27ae60; }
    .action-taken_outside { color: #e74c3c; }
    .action-misplaced { color: #f39c12; }
    .action-unreturned { color: #c0392b; }
    .action-unauthorized { color: #8e44ad; }
  </style>
</head>
<body>

  <h1>📜 RFID Activity Logs</h1>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>RFID Tag</th>
        <th>Book Title</th>
        <th>Reader ID</th>
        <th>Action</th>
        <th>Timestamp</th>
      </tr>
    </thead>
    <tbody id="logsTableBody">
      <tr><td colspan="6">Loading...</td></tr>
    </tbody>
  </table>

  <script>
    fetch('../Controllers/LogsController.php') // 👈 Make sure this matches your PHP file name
      .then(res => res.json())
      .then(logs => {
        const tbody = document.getElementById('logsTableBody');
        tbody.innerHTML = "";

        if (!Array.isArray(logs)) {
          throw new Error("Unexpected data format: Expected an array.");
        }

        if (logs.length === 0) {
          tbody.innerHTML = '<tr><td colspan="6">No logs found.</td></tr>';
          return;
        }

        logs.forEach((log, index) => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${index + 1}</td>
            <td class="tag">${log.rfid_tag}</td>
            <td>${log.book_title}</td>
            <td>${log.reader_id}</td>
            <td class="action-${log.action_type}">${log.action_type.replace('_', ' ').toUpperCase()}</td>
            <td>${log.created_at}</td>
          `;
          tbody.appendChild(row);
        });
      })
      .catch(error => {
        console.error("Error loading logs:", error);
        document.getElementById('logsTableBody').innerHTML = `
          <tr><td colspan="6">Error loading logs.</td></tr>
        `;
      });
  </script>

</body>
</html>
