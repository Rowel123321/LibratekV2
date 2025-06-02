<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if ($_SESSION['user_role'] !== 'admin') {
  header("Location: dashboard.php");
  exit;
}

$userName = $_SESSION['user_name'] ?? 'Unknown';
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter&display=swap" />
  <link rel="stylesheet" href="../CSS/styles.css" />
  <style>
    :root {
      --bg-main: #ffffff;
      --bg-modal: #ffffff;
      --text-main: #000000;
      --text-muted: #888888;
      --btn-bg: #007bff;
      --btn-text: #ffffff;
      --btn-bg-secondary: #dddddd;
      --table-header-bg: #f0f0f0;
    }

    [data-theme="dark"] {
      --bg-main: #1e1e1e;
      --bg-modal: #2a2a2a;
      --text-main: #f0f0f0;
      --text-muted: #bbbbbb;
      --btn-bg: #3391ff;
      --btn-text: #ffffff;
      --btn-bg-secondary: #444444;
      --table-header-bg: #333333;
    }
    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }


    body { font-family: Arial, sans-serif; margin: 20px; }

    .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
    .tabs button {
      padding: 10px 20px;
      border: none;
      background: #ddd;
      cursor: pointer;
      font-weight: bold;
    }
    .tabs button.active {
      background: #007bff;
      color: white;
    }

    .tab-content { display: none; }
    .tab-content.active { display: block; }

    table, th, td { border: 1px solid #999; border-collapse: collapse; padding: 8px; }
    table { width: 100%; margin-top: 10px; }
    th { background-color: #f0f0f0; }

    input, select {
      width: 100%;
      padding: 6px;
      margin: 6px 0;
    }
    form { max-width: 600px; margin: auto; }
   button[type="submit"] {
      margin-top: 10px;
      padding: 8px 12px;
      background: var(--btn-bg);
      color: var(--btn-text);
      border: none;
    }

    .edit-btn {
      color: var(--btn-bg);
      cursor: pointer;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }
    .modal-content {
      background: var(--bg-modal);
      padding: 20px 30px;
      border-radius: 10px;
      width: 400px;
      position: relative;
      color: var(--text-main);
    }
    .close-btn {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 20px;
      cursor: pointer;
      color: var(--text-muted);
    }
    .modal label{
      text-align: left;
      display: block;
    }
    .pagination-controls {
      display: flex;
      justify-content: right;
      gap: 10px;
      margin: 20px 0;
      flex-wrap: wrap;
      font-family: sans-serif;
    }

    .pagination-controls button {
      padding: 6px 10px;
      border: 1px solid #ccc;
      background: white;
      color: #007bff;
      cursor: pointer;
      border-radius: 5px;
      transition: background 0.3s;
    }

    .pagination-controls button:hover:not(:disabled) {
      background-color: #f0f0f0;
    }

    .pagination-controls button.active,
    .pagination-controls button.active-page {
      background: var(--btn-bg);
      color: var(--btn-text);
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
</head>
<body>

<!-- 🔒 Logout Confirmation Modal -->
<div class="modal" id="logoutModal" onclick="clickOutsideLogout(event)">
  <div class="modal-content">
    <span class="close-btn" onclick="closeLogoutModal()">×</span>
    <h3>Are you sure you want to logout?</h3>
    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
      <button onclick="confirmLogout()" style="background: #e74c3c; color: white; padding: 8px 14px;">Yes, Logout</button>
      <button onclick="closeLogoutModal()" style="background: #ccc; padding: 8px 14px;">Cancel</button>
    </div>
  </div>
</div>

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
  <a href="manage_books.php" class="<?= $currentPage === 'manage_books.php' ? 'active' : '' ?>">
    <i class="fas fa-folder"></i><span>Manage Books</span>
  </a>
  <div class="spacer"></div>
  <a href="#" onclick="directLogout(event)" class="logout-btn">
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

<div class="main-content">
<div class="tabs">
  
  
  <button class="tab-btn active" onclick="document.getElementById('addBookModal').style.display='flex'">➕ Add Book </button>
</div>

<div id="listTab" class="tab-content active">
  <table id="bookTable">
    <thead>
      <tr>
        <th>Reader ID</th>
        <th>Book Title</th>
        <th>Complete Title</th>
        <th>Author</th>
        <th>Tag</th>
        <th>Year</th>
        <th>Course</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <tr><td colspan="8">Loading...</td></tr>
    </tbody>
  </table>
</div>

<!-- Trigger button (optional) -->


<!-- Modal -->
<div id="addBookModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="document.getElementById('addBookModal').style.display='none'">&times;</span>
    <h2 style="margin-top: 0;">Add Book</h2>
    <form id="addForm">
      <label>Reader ID: <input name="reader_id" required /></label>
      <label>Book Title: <input name="book_title" required /></label>
      <label>Complete Title: <input name="complete_book_title" required /></label>
      <label>Author: <input name="author" required /></label>
      <label>Tag: <input name="assigned_tag" required /></label>
      <label>Year: <input name="year" type="number" id="yearAdd" required /></label>
      <label>Course:
        <select name="course" required>
          <option value="">-- Select Course --</option>
          <option value="BSIT">BSIT</option>
          <option value="BSCS">BSCS</option>
          <option value="BLIS">BLIS</option>
          <option value="DIT">DIT</option>
          <option value="MLIS">MLIS</option>
        </select>
      </label>
      <button type="submit">➕ Add Book</button>
    </form>
  </div>
</div>  

<div class="modal" id="editModal" onclick="clickOutsideToClose(event)">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal()">×</span>
    <h3 id="formTitle">Edit Book</h3>
    <form id="editForm">
      <input type="hidden" name="id" />
      <label>Reader ID: <input name="reader_id" required /></label>
      <label>Book Title: <input name="book_title" required /></label>
      <label>Complete Title: <input name="complete_book_title" required /></label>
      <label>Author: <input name="author" required /></label>
      <label>Tag: <input name="assigned_tag" required /></label>
      <label>Year: <input name="year" type="number" id="yearEdit" required /></label>
      <label>Course:
        <select name="course" required>
          <option value="">-- Select Course --</option>
          <option value="BSIT">BSIT</option>
          <option value="BSCS">BSCS</option>
          <option value="BLIS">BLIS</option>
          <option value="DIT">DIT</option>
          <option value="MLIS">MLIS</option>
        </select>
      </label>
      <button type="submit"> Save Changes</button>
    </form>
  </div>
</div>

<div id="pagination" class="pagination-controls"></div>

<script>
  const tableBody = document.querySelector("#bookTable tbody");
  const tabButtons = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");
  const addForm = document.getElementById("addForm");
  const editForm = document.getElementById("editForm");
  const editModal = document.getElementById("editModal");
  let existingReaderIds = [];
  let currentPage = 1;
  let totalPages = 1;
  const limitPerPage = 10;

 

  function directLogout(event) {
  event.preventDefault();
  document.getElementById('logoutModal').style.display = 'flex';
}

function closeLogoutModal() {
  document.getElementById('logoutModal').style.display = 'none';
}

function clickOutsideLogout(e) {
  const modal = document.getElementById('logoutModal');
  if (e.target === modal) closeLogoutModal();
}

function confirmLogout() {
  fetch('../Controllers/UserController.php?action=logout')
    .then(res => res.json())
    .then(() => window.location.href = 'login.php')
    .catch(err => alert('Logout failed: ' + err.message));
}


  function loadBooks() {
  fetch(`../Controllers/BookController.php?page=${currentPage}&limit=${limitPerPage}`)
    .then(res => res.json())
    .then(response => {
      let data = response.books || response; // handle both array and object format
      const totalItems = response.total || data.length;

      existingReaderIds = data.map(book => book.reader_id.toString());
      tableBody.innerHTML = "";

      if (!Array.isArray(data)) {
        tableBody.innerHTML = `<tr><td colspan="8">❌ Error: ${response.message || 'Invalid response'}</td></tr>`;
        return;
      }

      if (data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="8">📭 No books found.</td></tr>`;
        return;
      }

      data.forEach(book => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${book.reader_id}</td>
          <td>${book.book_title}</td>
          <td>${book.complete_book_title}</td>
          <td>${book.author}</td>
          <td>${book.assigned_tag}</td>
          <td>${book.year}</td>
          <td>${book.course}</td>
          <td><span class="edit-btn" onclick='editBook(${JSON.stringify(book)})'>✏️ Edit</span></td>
        `;
        tableBody.appendChild(row);
      });

      updatePagination(totalItems);
    })
    .catch(err => {
      tableBody.innerHTML = `<tr><td colspan="8">❌ Fetch error: ${err.message}</td></tr>`;
    });
  }


  function editBook(book) {
    for (const key in book) {
      if (editForm[key]) editForm[key].value = book[key];
    }
    editModal.style.display = 'flex';
  }

  function closeModal() {
    editModal.style.display = 'none';
    editForm.reset();
  }

  function clickOutsideToClose(e) {
    if (e.target === editModal) closeModal();
  }

  addForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(addForm);
    const data = Object.fromEntries(formData.entries());

    if (isNaN(data.reader_id)) {
      alert("Reader ID must be a number.");
      return;
    }
    if (existingReaderIds.includes(data.reader_id)) {
      alert("Reader ID already exists.");
      return;
    }

    fetch('../Controllers/BookController.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    .then(res => res.json())
      .then(res => {
      alert(res.message || 'Book added!');
      addForm.reset();
      document.getElementById('addBookModal').style.display = 'none'; // ✅ Hide modal
      loadBooks();
    })
    .catch(err => alert("Add error: " + err.message));
  });

  editForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(editForm);
    const data = Object.fromEntries(formData.entries());

    if (isNaN(data.reader_id)) {
      alert("Reader ID must be a number.");
      return;
    }

    fetch('../Controllers/BookController.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
      alert(res.message || 'Updated!');
      closeModal();
      loadBooks();
    })
    .catch(err => alert("Update error: " + err.message));
  });

  loadBooks();

  const currentYear = new Date().getFullYear();
  const yearMin = currentYear - 30;
  const yearMax = currentYear + 30;

  const yearAddInput = document.getElementById("yearAdd");
  const yearEditInput = document.getElementById("yearEdit");

  yearAddInput.min = yearMin;
  yearAddInput.max = yearMax;
  yearAddInput.placeholder = `${yearMin} - ${yearMax}`;
  yearEditInput.min = yearMin;
  yearEditInput.max = yearMax;
  yearEditInput.placeholder = `${yearMin} - ${yearMax}`;

  function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('collapsed');
    const theme = document.documentElement.getAttribute('data-theme');
    updateLogo(theme);
  }

  const savedTheme = localStorage.getItem('theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
  updateThemeButton(savedTheme);

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
    let logoName = isCollapsed
      ? (theme === 'dark' ? 'cbook.png' : 'cbook1.png')
      : (theme === 'dark' ? 'logo1.png' : 'logo2.png');
    logo.style.width = isCollapsed ? '40px' : '130px';
    logo.src = `../Images/${logoName}`;
  }

  updateLogo(savedTheme);

  const dropdown = document.getElementById('userDropdown');
  function toggleUserDropdown() {
    dropdown.classList.toggle('hidden');
  }

  const themeToggleDropdown = document.getElementById('themeToggleDropdown');
  themeToggleDropdown.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    applyTheme(next);
  });

  document.addEventListener('click', function (event) {
    if (!document.querySelector('.user-menu').contains(event.target)) {
      dropdown.classList.add('hidden');
    }
  });

 function updatePagination(totalItems) {
  const paginationContainer = document.getElementById('pagination');
  paginationContainer.innerHTML = '';

  const totalPages = Math.ceil(totalItems / limitPerPage);
  const maxVisiblePages = 3;

  let startPage = Math.max(1, currentPage - 1);
  let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

  // Adjust if near the end
  if (endPage >= totalPages - 1) {
    endPage = totalPages - 1;
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }

  const createBtn = (text, page, disabled = false, isActive = false) => {
    const btn = document.createElement('button');
    btn.textContent = text;
    btn.classList.add('pagination-btn');
    if (isActive) btn.classList.add('active');
    btn.disabled = disabled;
    btn.addEventListener('click', () => {
      if (!disabled) {
        currentPage = page;
        loadBooks();
      }
    });
    return btn;
  };

  // First and Prev buttons
  paginationContainer.appendChild(createBtn('«', 1, currentPage === 1));
  paginationContainer.appendChild(createBtn('‹', currentPage - 1, currentPage === 1));

  // Page buttons (sliding window)
  for (let i = startPage; i <= endPage; i++) {
    paginationContainer.appendChild(createBtn(i, i, false, i === currentPage));
  }

  // Ellipsis if needed
  if (endPage < totalPages - 1) {
    const ellipsis = document.createElement('span');
    ellipsis.textContent = '...';
    ellipsis.className = 'ellipsis';
    paginationContainer.appendChild(ellipsis);
  }

  // Last page button (if more than one page)
  if (totalPages > 1) {
    paginationContainer.appendChild(createBtn(totalPages, totalPages, false, currentPage === totalPages));
  }

  // Next and Last buttons
  paginationContainer.appendChild(createBtn('›', currentPage + 1, currentPage === totalPages));
  paginationContainer.appendChild(createBtn('»', totalPages, currentPage === totalPages));
}



</script>

</body>
</html>
