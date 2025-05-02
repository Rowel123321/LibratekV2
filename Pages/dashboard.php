<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
$userName = $_SESSION['user_name'] ?? 'Unknown'; // Default to 'Unknown' if not set

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Capstone Projects Shelf - Dashboard</title>  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../CSS/styles.css" />
</head>

<body>
<div class="sidebar">
  <div class="logo">
    <img id="themeLogo" src="../Images/logo1.png" alt="LibraTek Logo" class="logo-img" />
    <div class="section-label">Platform</div>
  </div>

  <div style="text-align: center; margin-bottom: 10px;">
  <button id="clearAllBtn" class="clear-btn">Check Unreturned Book</button>
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

  <!-- User Dropdown -->
  <div class="user-menu">
    <i class="fas fa-user-circle user-icon" onclick="toggleUserDropdown()"></i>
    <div id="userDropdown" class="user-dropdown hidden">
      <div class="user-name">👤 <?php echo htmlspecialchars($userName); ?></div>
      <button class="theme-toggle" id="themeToggleDropdown">🌙 Dark Mode</button>
    </div>
  </div>
</div>
  
  <div class="container">
  <div class="program-filter">
  <h2>CHOOSE A PROGRAM</h2>
  <div class="custom-select-wrapper">
    <select id="programSelect" onchange="filterProgram(this.value)">
      <option value="all">📚 All Programs</option>
      <option value="BSIT">🖥️ BSIT</option>
      <option value="BSCS">💻 BSCS</option>
      <option value="BLIS">📖 BLIS</option>
      <option value="MSIT">📖 MSIT</option>
      <option value="DIT">📖 DIT</option>
      <option value="MLIS">📖 MLIS</option>
    </select>
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
<hr class="section-divider">
</div>
    <div id="shelves-by-year"></div>
  </div>

  <script>
    // 🧠 LOGOUT FUNCTION
    function logout(event) {
      event.preventDefault(); // ⛔ stop the browser from following the link
      document.getElementById('logoutModal').classList.remove('hidden');
    }

    function closeLogoutModal() {
      document.getElementById('logoutModal').classList.add('hidden');
    }

    function confirmLogout() {
      fetch('../Controllers/UserController.php?action=logout')
        .then(res => res.json())
        .then(data => {
          window.location.href = 'login.php';
        })
        .catch(err => {
          console.error(err);
          alert('Logout failed.');
        });
}
    // 📚 LOAD BOOK SHELVES
    const container = document.getElementById('shelves-by-year');
    const courseList = ['BSIT', 'BSCS', 'BLIS','MSIT' ,'DIT' ,'MLIS'];
    const bookMap = new Map();
    const tagToBook = new Map();

    fetch('../Controllers/BooksController.php')
      .then(response => response.json())
      .then(data => {
        const grouped = {};

        data.forEach(book => {
          if (!grouped[book.year]) grouped[book.year] = {};
          if (!grouped[book.year][book.course]) grouped[book.year][book.course] = [];
          grouped[book.year][book.course].push(book);

        if (book.assigned_tag) {
  tagToBook.set(book.assigned_tag.trim(), book);
}

        });

        for (const year in grouped) {
          const yearSection = document.createElement('div');
          yearSection.className = 'year-section';

        

          const coursesContainer = document.createElement('div');
          coursesContainer.className = 'courses-container';

          courseList.forEach(course => {
            const column = document.createElement('div');
            column.className = 'course-column';
            column.setAttribute('data-course', course);

          const label = document.createElement('div');
          label.className = 'course-label';
          label.textContent = `${course}-${year}`;
          column.appendChild(label);

          const booksWrapper = document.createElement('div');
          booksWrapper.className = 'books';

          (grouped[year][course] || []).forEach(book => {
            const bookDiv = document.createElement('div');
            bookDiv.className = 'book';

            const titleEl = document.createElement('div');
            titleEl.className = 'title';
            titleEl.textContent = book.book_title;

            const statusEl = document.createElement('div');
            statusEl.className = 'status';

            const originEl = document.createElement('div');
            originEl.className = 'origin';

            const readerEl = document.createElement('div');
            readerEl.className = 'reader';

       const modal = document.createElement('div');
modal.className = 'book-modal';
modal.innerHTML = `
  <div><strong>${book.complete_book_title}</strong></div>
  <div> Author: ${book.author || 'Unknown'}</div>
  <div> ${book.course}</div>
  <div> Year ${book.year}</div>

`;


            bookDiv.appendChild(modal);

            bookDiv.appendChild(modal);
            bookDiv.appendChild(titleEl);

            bookMap.set(book.book_title, {
              el: bookDiv,
              status: statusEl,
              origin: originEl,
              reader: readerEl
            });

            booksWrapper.appendChild(bookDiv);
          });

          column.appendChild(booksWrapper);
          coursesContainer.appendChild(column);
        });
          yearSection.appendChild(coursesContainer);
          container.appendChild(yearSection);
        }
      });

    // 🔁 LIVE BOOK STATUS CHECK
    setInterval(() => {
      fetch('../Controllers/BooksController.php')
        .then(response => response.json())
        .then(data => {
          const now = Date.now();

          data.forEach(book => {
            const entry = bookMap.get(book.book_title);
            if (!entry) return;

            const { el, status, origin, reader } = entry;
            const assigned = (book.assigned_tag || '').trim();
            const scanned = (book.scanned_tag || '').trim();
            const readerId = book.reader_id || '';

            const scannedTime = book.last_scanned_at
              ? new Date(book.last_scanned_at.replace(' ', 'T')).getTime()
              : 0;
            const diff = now - scannedTime;

            el.classList.remove('unscanned', 'matched', 'mismatched', 'unreturned', 'misplaced');
            origin.textContent = '';
            reader.textContent = '';

            if (!scanned && !book.last_scanned_at) {
  el.classList.add('unreturned');
  status.textContent = '🔴 Unreturned';
}
 else if (!scanned) {
              el.classList.add('unscanned');
              status.textContent = '⚪ Empty';
            } else if (scanned === assigned) {
              el.classList.add('matched');
              status.textContent = '✅ Matched';
              reader.textContent = `📡 Reader: ${readerId}`;
        } else {
  el.classList.add('misplaced');
  status.textContent = '⚠️ Misplaced';
  reader.textContent = `📡 Reader: ${readerId}`;

  const correct = tagToBook.get(scanned);

  if (correct) {
    origin.textContent = `📍 Tag belongs to: ${correct.book_title} (${correct.course}, Year ${correct.year})`;

    // Update modal
    const modal = el.querySelector('.book-modal');
    modal.innerHTML = `
  <div><strong>${correct.complete_book_title}</strong></div>
  <div> Author: ${correct.author || 'Unknown'}</div>
  <div style="margin-top: 6px; font-style: italic; color: #aaa;">
     This book belongs to ${correct.course}, Year ${correct.year}
  </div>
`;


    // ✅ Also update the visible title
    const titleEl = el.querySelector('.title');
    if (titleEl) titleEl.textContent = correct.book_title;

  } else {
    origin.textContent = `❌ Tag not assigned anywhere`;

    const modal = el.querySelector('.book-modal');
    modal.innerHTML = `
      <div><strong>Unknown Book</strong></div>
      <div> Author: Unknown</div>
      <div> Unknown Course</div>
      <div> Year: N/A</div>
    `;

    const titleEl = el.querySelector('.title');
    if (titleEl) titleEl.textContent = 'Unknown';
  }
}



          });
        });
    }, 500);

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

    function filterProgram(courseName) {
      const columns = document.querySelectorAll('.course-column');
      columns.forEach(col => {
        const course = col.getAttribute('data-course');
        col.style.display = (courseName === 'all' || course === courseName) ? 'block' : 'none';
      });
    }
    






    document.getElementById('clearAllBtn').addEventListener('click', () => {
  if (!confirm('Are you sure you want to clear last scanned time for all unscanned books?')) return;

  fetch('../Controllers/ClearScannedAtController.php', {
    method: 'POST'
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Unreturned Book Highligh Color Red');
      location.reload(); // or re-fetch your book data manually
    } else {
      alert('Failed: ' + data.error);
    }
  })
  .catch(() => alert('Request failed.'));
});




  </script>
</body>
</html>
