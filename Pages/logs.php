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
  <div class="alert-icon">⚠️</div>
  <div id="alertText">Book taken outside</div>
  <button id="closeBtn">Dismiss</button>
</div>
<!-- 📦 Book Taken Outside Modal -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Logs</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter&display=swap" />
  <link rel="stylesheet" href="../CSS/styles.css" />
  <style>
  .pagination-controls {
    display: flex;
    justify-content: right;
    align-items: right;
    gap: 4px;
    margin: 20px 20px 20px 0;
    flex-wrap: wrap;
    font-family: sans-serif;
  }

  .pagination-controls button {
    padding: 5px 10px;
    border: 1px solid #ccc;
    background: white;
    color: #007bff;
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.3s;
  }

  .pagination-controls button:hover:not(:disabled) {
    background-color: #f0f0f0;
  }

  .pagination-controls button.active-page {
    background-color: #007bff;
    color: white;
    font-weight: bold;
  }

  .pagination-controls button:disabled {
    color: #aaa;
    background-color: #f9f9f9;
    cursor: default;
  }

  .pagination-controls .ellipsis {
    padding: 0 6px;
    color: #666;
    font-weight: bold;
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
  <i class="fas fa-file-alt"></i>
  <span>Logs</span>
  <span id="logBadge" class="log-badge hidden">0</span>
</a>
    <?php if ($_SESSION['user_role'] === 'admin'): ?>
  <a href="manage_books.php" class="<?= $currentPage === 'manage_books' ? 'active' : '' ?>">
    <i class="fas fa-folder"></i><span>Manage Books</span>
  </a>
<?php endif; ?>

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


<!-- 🔽 Filters -->
<div class="filters">
  <label>Action:
    <select id="actionFilter" onchange="applyFilters()">
      <option value="">All Actions</option>
      <option value="normal">Reshelved</option>
      <option value="taken_outside">Taken Outside</option>
      <option value="misplaced">Misplaced</option>
      <option value="unreturned">Unreturned</option>
      <option value="removed">Currently In Use</option>
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
      <th>Date/Time</th>
    </tr>
  </thead>
  <tbody id="logsTableBody">
    <tr><td colspan="6">Loading...</td></tr>
  </tbody>
</table>

<div id="pagination" class="pagination-controls"></div>

<script>
  let currentPage = 1;
  let totalPages = 1;
  const limitPerPage = 10;

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

  function applyFilters(page = 1) {
  currentPage = page;
  const action = document.getElementById('actionFilter').value;
  const date = document.getElementById('dateFilter').value;

  const params = new URLSearchParams();
  if (action) params.append('action_type', action);
  if (date) params.append('date', date);
  params.append('page', currentPage);
  params.append('limit', limitPerPage);

  fetch(`../Controllers/LogsController.php?${params.toString()}`)
    .then(res => res.json())
    .then(data => {
      updateTable(data.logs);
      updatePagination(data.total);
    })
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
    case 'removed': return 'Currently In Use';
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
  const options = {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: 'numeric', minute: 'numeric', second: 'numeric',
    hour12: true,
  };
  return date.toLocaleString('en-US', options);
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
            showModal(`Book "${bookTitle}" was taken outside`);

            // Show/update badge
            const badge = document.getElementById('logBadge');
            const currentCount = parseInt(badge.textContent || '0', 10);
            badge.textContent = currentCount + 1;
            badge.classList.remove('hidden');
          }
          lastSeenTime = timestamp;
        }
      }
    })
    .catch(error => console.error('Fetch error:', error));
}, 1000);

document.querySelector('a[href="logs.php"]').addEventListener('click', () => {
  const badge = document.getElementById('logBadge');
  badge.textContent = '0';
  badge.classList.add('hidden');
});

function updatePagination(totalItems) {
  const paginationContainer = document.getElementById('pagination');
  paginationContainer.innerHTML = '';

  totalPages = Math.ceil(totalItems / limitPerPage);

  const maxVisiblePages = 3; // Only 3 pages visible at once (excluding first/last buttons)
  let startPage = Math.max(1, currentPage - 1);
  let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

  // Adjust if near the end
  if (endPage >= totalPages - 1) {
    endPage = totalPages - 1;
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }

  const createBtn = (text, page, isActive = false, disabled = false) => {
    const btn = document.createElement('button');
    btn.textContent = text;
    btn.disabled = disabled;
    btn.className = isActive ? 'active-page' : '';
    btn.addEventListener('click', () => {
      if (!disabled) applyFilters(page);
    });
    return btn;
  };

  // First and Prev buttons
  paginationContainer.appendChild(createBtn('«', 1, false, currentPage === 1));
  paginationContainer.appendChild(createBtn('‹', currentPage - 1, false, currentPage === 1));

  // Page numbers range
  for (let i = startPage; i <= endPage; i++) {
    paginationContainer.appendChild(createBtn(i, i, i === currentPage));
  }

  // Ellipsis and last page
  if (endPage < totalPages - 1) {
    const ellipsis = document.createElement('span');
    ellipsis.textContent = '...';
    ellipsis.className = 'ellipsis';
    paginationContainer.appendChild(ellipsis);
  }

  if (totalPages > 1) {
    paginationContainer.appendChild(createBtn(totalPages, totalPages, currentPage === totalPages));
  }

  // Next and Last buttons
  paginationContainer.appendChild(createBtn('›', currentPage + 1, false, currentPage === totalPages));
  paginationContainer.appendChild(createBtn('»', totalPages, false, currentPage === totalPages));
}


</script>

</body>
</html>
