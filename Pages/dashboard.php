<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
$userName = $_SESSION['user_name'] ?? 'Unknown'; // Default to 'Unknown' if not set
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
    <a href="dashboard.php"><i class="fas fa-chart-bar"></i><span>Dashboard</span></a>
    <a href="logs.php"><i class="fas fa-file-alt"></i><span>Logs</span></a>
    <div class="spacer"></div>
    <a href="logout.php" onclick="logout()" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
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
    <div id="shelves-by-year"></div>
  </div>

  <script>
    // 🧠 LOGOUT FUNCTION
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

    // 📚 LOAD BOOK SHELVES
    const container = document.getElementById('shelves-by-year');
    const courseList = ['BSIT', 'BSCS', 'BLIS'];
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
            tagToBook.set(book.assigned_tag.trim(), {
              title: book.book_title,
              course: book.course,
              year: book.year
            });
          }
        });

        for (const year in grouped) {
          const yearSection = document.createElement('div');
          yearSection.className = 'year-section';

          const yearHeading = document.createElement('h2');
          yearHeading.textContent = `Year ${year}`;
          yearSection.appendChild(yearHeading);

          const coursesContainer = document.createElement('div');
          coursesContainer.className = 'courses-container';

          courseList.forEach(course => {
          const column = document.createElement('div');
          column.className = 'course-column';

          const label = document.createElement('div');
          label.className = 'course-label';
          label.textContent = course;
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
              <div><strong>${book.book_title}</strong></div>
              <div>📚 ${book.course}</div>
              <div>🗓️ Year ${book.year}</div>
              <div>🆔 Tag: ${book.assigned_tag || 'N/A'}</div>
              <div>🛰️ Reader: ${book.reader_id || 'N/A'}</div>
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

            if (!scanned && scannedTime && diff >= 10000) {
              el.classList.add('unreturned');
              status.textContent = '🔴 Unreturned';
            } else if (!scanned) {
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
              origin.textContent = correct
                ? `📍 Tag belongs to: ${correct.title} (${correct.course}, Year ${correct.year})`
                : `❌ Tag not assigned anywhere`;
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
  </script>
</body>
</html>
