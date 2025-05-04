<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
$userName = $_SESSION['user_name'] ?? 'Unknown';
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- 📦 Book Taken Outside Modal -->
<div id="alertModal">
  <span id="alertText">Book taken outside</span><br/>
  <button id="closeBtn">Dismiss</button>
</div>
<!-- 📦 Book Taken Outside Modal -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>RFID Scan Logs</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter&display=swap" />
  <link rel="stylesheet" href="../CSS/styles.css" />
  <style>
#alertModal {
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  padding: 30px 40px;
  border-radius: 12px;
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
  z-index: 1000;
  font-size: 20px;
}

#closeBtn {
  display: inline-block;
  margin-top: 20px;
  padding: 8px 16px;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 16px;
}

#closeBtn:hover {
  background: #c0392b;
}

  </style>
  <script>
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
  </script>
</head>
<body>

<div class="sidebar">
  <div class="logo">
    <img id="themeLogo" src="../Images/logo1.png" alt="LibraTek Logo" class="logo-img" />
    <div class="section-label">Platform</div>
  </div>
  <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
    <i class="fas fa-chart-bar"></i><span>Dashboard</span>
  </a>
  <a href="logs.php" class="<?= $currentPage === 'logs.php' ? 'active' : '' ?>">
    <i class="fas fa-file-alt"></i><span>Logs</span>
  </a>
  <div class="spacer"></div>
  <a href="#" onclick="logout(event)" class="logout-btn">
    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
  </a>
</div>

<div class="topbar">
  <button class="toggle-btn" onclick="toggleSidebar()">
    <i class="fas fa-table-columns" style="font-size: 18px;"></i>
  </button>
  <div class="user-menu">
    <i class="fas fa-user-circle user-icon" onclick="toggleUserDropdown()"></i>
    <div id="userDropdown" class="user-dropdown hidden">
      <div class="user-name">👤 <?php echo htmlspecialchars($userName); ?></div>
      <button class="theme-toggle" id="themeToggleDropdown">🌙 Dark Mode</button>
    </div>
  </div>
</div>

<div id="logoutModal" class="modal hidden">
  <div class="modal-content">
    <i class="fas fa-sign-out-alt modal-icon"></i>
    <p>Are you sure you want to logout?</p>
    <div class="modal-actions">
      <button onclick="confirmLogout()" class="confirm-btn">Yes</button>
      <button onclick="closeLogoutModal()" class="cancel-btn">No</button>
    </div>
  </div>
</div>

<h1>📜 RFID Activity Logs</h1>

<!-- 🔽 Filters -->
<div class="filters">
  <label>Action:
    <select id="actionFilter" onchange="applyFilters()">
      <option value="">All Actions</option>
      <option value="normal">Reshelved</option>
      <option value="taken_outside">Taken Outside</option>
      <option value="misplaced">Misplaced</option>
      <option value="unreturned">Unreturned</option>
      <option value="removed">Removed</option>
    </select>
  </label>

  <label>Date:
    <input type="date" id="dateFilter" onchange="applyFilters()" />
  </label>
</div>

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
  function logout(event) {
    event.preventDefault();
    document.getElementById('logoutModal').classList.remove('hidden');
  }

  function closeLogoutModal() {
    document.getElementById('logoutModal').classList.add('hidden');
  }

  function confirmLogout() {
    fetch('../Controllers/UserController.php?action=logout')
      .then(res => res.json())
      .then(() => window.location.href = 'login.php')
      .catch(err => {
        console.error(err);
        alert('Logout failed.');
      });
  }

  function applyFilters() {
    const action = document.getElementById('actionFilter').value;
    const date = document.getElementById('dateFilter').value;

    const params = new URLSearchParams();
    if (action) params.append('action_type', action);
    if (date) params.append('date', date);

    fetch(`../Controllers/LogsController.php?${params.toString()}`)
      .then(res => res.json())
      .then(updateTable)
      .catch(error => {
        console.error("Error loading logs:", error);
        document.getElementById('logsTableBody').innerHTML =
          `<tr><td colspan="6">Error loading logs.</td></tr>`;
      });
  }
  function getActionLabel(action) {
  switch (action) {
    case 'normal': return 'Reshelved';
    case 'taken_outside': return 'Taken Outside';
    case 'misplaced': return 'Misplaced';
    case 'unreturned': return 'Unreturned';
    case 'unauthorized': return 'Unauthorized';
    case 'reshelved': return 'Reshelved';
    case 'removed': return 'Removed';
    default: return action.replace('_', ' ').toUpperCase();
  }
}

  function updateTable(logs) {
    const tbody = document.getElementById('logsTableBody');
    tbody.innerHTML = "";

    if (!Array.isArray(logs) || logs.length === 0) {
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
<td class="action-${log.action_type}">${getActionLabel(log.action_type)}</td>
        <td>${formatDate(log.created_at)}</td>
      `;
      tbody.appendChild(row);
    });
  }

  function formatDate(isoString) {
    const date = new Date(isoString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
  }

  function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('collapsed');
    const theme = document.documentElement.getAttribute('data-theme');
    updateLogo(theme);
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
    let logoName = isCollapsed ? (theme === 'dark' ? 'cbook.png' : 'cbook1.png') : (theme === 'dark' ? 'logo1.png' : 'logo2.png');
    logo.src = `../Images/${logoName}`;
    logo.style.width = isCollapsed ? '40px' : '130px';
  }

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

  // Initial load
  applyFilters();

  let lastSeenTime = null;

function showModal(message) {
  const modal = document.getElementById('alertModal');
  const text = document.getElementById('alertText');
  text.textContent = message;
  modal.style.display = 'block';
}

function hideModal() {
  document.getElementById('alertModal').style.display = 'none';
}

document.getElementById('closeBtn').addEventListener('click', hideModal);

// 🔁 Poll for new scans every second
setInterval(() => {
  fetch('../Controllers/ExitZoneController.php')
    .then(response => response.json())
    .then(data => {
      if (data.length > 0) {
        const latest = data[0];
        const timestamp = latest.scanned_at;
        const bookTitle = latest.book_title;

        if (timestamp && timestamp !== lastSeenTime) {
          if (lastSeenTime !== null) {
            showModal(`📦 Book "${bookTitle}" was taken outside`);
          }
          lastSeenTime = timestamp;
        }
      }
    })
    .catch(error => console.error('Fetch error:', error));
}, 1000);



</script>

</body>
</html>
