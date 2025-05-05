<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
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
    }

    .edit-btn { color: blue; cursor: pointer; }

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
      background: #fff;
      padding: 20px 30px;
      border-radius: 10px;
      width: 400px;
      position: relative;
    }
    .close-btn {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 20px;
      cursor: pointer;
      color: #888;
    }
  </style>
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
  <a href="manage_books.php" class="<?= $currentPage === 'manage_books' ? 'active' : '' ?>">
  <i class="fas fa-folder"></i><span>Manage Books</span>
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


<div class="main-content">
<div class="tabs">
  <button class="tab-btn active" onclick="switchTab('listTab')">📋 Book List</button>
  <button class="tab-btn" onclick="switchTab('addTab')">➕ Add Book</button>
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

    <div id="addTab" class="tab-content">
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
      <button type="submit">📎 Save Changes</button>
    </form>
  </div>
</div>

<script>
  const tableBody = document.querySelector("#bookTable tbody");
  const tabButtons = document.querySelectorAll(".tab-btn");
  const tabContents = document.querySelectorAll(".tab-content");
  const addForm = document.getElementById("addForm");
  const editForm = document.getElementById("editForm");
  const editModal = document.getElementById("editModal");
  let existingReaderIds = [];

  function switchTab(tabId) {
    tabButtons.forEach(btn => btn.classList.remove("active"));
    tabContents.forEach(tab => tab.classList.remove("active"));
    document.querySelector(`.tab-btn[onclick*="${tabId}"]`).classList.add("active");
    document.getElementById(tabId).classList.add("active");
  }
  function logout(event) {
  event.preventDefault();
  document.getElementById('logoutModal').classList.remove('hidden'); // Make it visible
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


  function loadBooks() {
    fetch('../Controllers/BookController.php')
      .then(res => res.json())
      .then(data => {
        existingReaderIds = data.map(book => book.reader_id.toString());
        tableBody.innerHTML = "";
        if (!Array.isArray(data)) {
          tableBody.innerHTML = `<tr><td colspan="8">❌ Error: ${data.message || 'Invalid response'}</td></tr>`;
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
      alert("Reader ID already exists. Use a unique one.");
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
      loadBooks();
      switchTab('listTab');
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

  // Dynamically set min/max for year inputs
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
    updateLogo(theme); // ✅ This handles all image switching
  }
   // Initial theme setup
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeButton(savedTheme);

    function applyTheme(theme) {
      document.documentElement.setAttribute('data-theme', theme);
      localStorage.setItem('theme', theme);
      updateThemeButton(theme);
      updateLogo(theme);
    }
      
    // Update text on dropdown toggle
    function updateThemeButton(theme) {
      const toggleBtn = document.getElementById('themeToggleDropdown');
      if (!toggleBtn) return;

      if (theme === 'dark') {
        toggleBtn.textContent = '☀️ Light Mode';
      } else {
        toggleBtn.textContent = '🌙 Dark Mode';
      }
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

    updateLogo(savedTheme);

    // Dropdown toggle
    const dropdown = document.getElementById('userDropdown');
    function toggleUserDropdown() {
      dropdown.classList.toggle('hidden');
    }

    // Inside dropdown: Theme Toggle
    const themeToggleDropdown = document.getElementById('themeToggleDropdown');
    themeToggleDropdown.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-theme');
      const next = current === 'dark' ? 'light' : 'dark';
      applyTheme(next);
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function (event) {
      const isClickInside = document.querySelector('.user-menu').contains(event.target);
      if (!isClickInside) {
        dropdown.classList.add('hidden');
      }
    });

</script>

</body>
</html>
