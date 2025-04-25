<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
$userName = $_SESSION['user_name'] ?? 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RFID Scan Logs</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter&display=swap" />
  <link rel="stylesheet" href="../CSS/styles.css" />

  <script>
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
  </script>
</head>
<body>

<div class="sidebar">
  <div class="logo">
    <img id="themeLogo" src="../Images/logo1.png" alt="LibraTek Logo" class="logo-img">
    <div class="section-label">Platform</div>
  </div>
  <a href="dashboard.php"><i class="fas fa-chart-bar"></i><span>Dashboard</span></a>
  <a href="logs.php"><i class="fas fa-file-alt"></i><span>Logs</span></a>
  <div class="spacer"></div>
  <a href="logout.php" onclick="logout()" class="logout-btn"><i class="fas fa-sign-out-alt"></i>Logout</a>
</div>

<div class="topbar">
  <button class="toggle-btn" onclick="toggleSidebar()">
    <i class="fas fa-table-columns" style="font-size: 18px;"></i>
  </button>

  <div class="user-menu">
    <i class="fas fa-user-circle user-icon" onclick="toggleUserDropdown()"></i>
    <div id="userDropdown" class="user-dropdown hidden">
      <div class="user-name">👤 <?php echo htmlspecialchars($userName); ?></div>
      <button class="theme-toggle" id="themeToggleDropdown">🌓</button>
    </div>
  </div>
</div>

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
  function logout() {
    fetch('../Controllers/UserController.php?action=logout')
      .then(res => res.json())
      .then(data => {
        alert(data.message);
        window.location.href = 'login.php';
      })
      .catch(err => {
        console.error(err);
        alert('Logout failed.');
      });
  }

  fetch('../Controllers/LogsController.php')
    .then(res => res.json())
    .then(logs => {
      const tbody = document.getElementById('logsTableBody');
      tbody.innerHTML = "";

      if (!Array.isArray(logs)) throw new Error("Unexpected data format: Expected an array.");

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

  function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('collapsed');

    const theme = document.documentElement.getAttribute('data-theme');
    updateLogo(theme); // Smart switch logo
  }

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    updateThemeButton(theme);
    updateLogo(theme);
  }

  function updateThemeButton(theme) {
    const toggleBtn = document.getElementById('themeToggleDropdown');
    if (!toggleBtn) return;
    toggleBtn.textContent = theme === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode';
  }

  function updateLogo(theme) {
    const logo = document.getElementById('themeLogo');
    const sidebar = document.querySelector('.sidebar');
    const isCollapsed = sidebar.classList.contains('collapsed');

    let logoName = '';
    if (isCollapsed) {
      logoName = theme === 'dark' ? 'cbook.png' : 'cbook1.png';
      logo.style.width = '40px';
    } else {
      logoName = theme === 'dark' ? 'logo1.png' : 'logo2.png';
      logo.style.width = '130px';
    }

    logo.src = `../Images/${logoName}`;
  }

  // Init
  const currentTheme = document.documentElement.getAttribute('data-theme');
  updateThemeButton(currentTheme);
  updateLogo(currentTheme);

  function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
  }

  document.getElementById('themeToggleDropdown').addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    applyTheme(next);
  });

  document.addEventListener('click', function (event) {
    const isClickInside = document.querySelector('.user-menu').contains(event.target);
    if (!isClickInside) {
      document.getElementById('userDropdown').classList.add('hidden');
    }
  });
</script>

</body>
</html>
