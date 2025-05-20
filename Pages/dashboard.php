<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
$userName = $_SESSION['user_name'] ?? 'Unknown'; // Default to 'Unknown' if not set

$currentPage = basename($_SERVER['PHP_SELF']);
?>
 <div id="alertModal">
  <div class="alert-icon">⚠️</div>
  <div id="alertText">Book taken outside</div>
  <button id="closeBtn">Dismiss</button>
</div>
    <style>
       /* Basic Modal Style */
  .modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4); /* Background overlay */
  }

  .modal-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 40%;
    border-radius: 8px;
    text-align: center;
  }

  .modal-icon {
    font-size: 30px;
    color: red;
    margin-bottom: 10px;
  }

  .modal-actions {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
  }

  .confirm-btn, .cancel-btn {
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
  }

  .confirm-btn {
    background-color: green;
    color: white;
  }

  .cancel-btn {
    background-color: red;
    color: white;
  }

  /* Hidden class for the modal */
  .hidden {
    display: none;
  }
    </style>
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

 <!-- Sidebar item with hover-reveal button -->
  <!-- Dashboard menu item -->
  <div class="sidebar-item-wrapper">
  <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
    <i class="fas fa-chart-bar"></i><span>Dashboard</span>
  </a>
</div>
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

    <div class="filter-bar">
      <div class="custom-select-wrapper">
        <select id="programSelect" onchange="filterProgram(this.value)">
          <option value="all">📚 All Programs</option>
          <option value="BSIT">🖥️ BSIT</option>
          <option value="BSCS">💻 BSCS</option>
          <option value="BLIS">📖 BLIS</option>
          <option value="MSIT">📖 MIT</option>
          <option value="DIT">📖 DIT</option>
        </select>
      </div>

      <span class="dot-separator">•</span>
      
     <button id="clearAllBtn" class="unreturned-btn" onclick="openUnreturnedModal()">Check Unreturned Book</button>
     <!-- Modal for Checking Unreturned Books -->
      <div id="unreturnedModal" class="modal hidden">
        <div class="modal-content">
          <i class="fas fa-exclamation-circle modal-icon"></i>
          <p>Are you sure you want to check all unreturned books?</p>
          <div class="modal-actions">
            <button onclick="clearUnreturnedBooks()" class="confirm-btn">Yes</button>
            <button onclick="closeUnreturnedModal()" class="cancel-btn">No</button>
          </div>
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
    const courseList = ['BSIT', 'BSCS', 'BLIS','MIT' ,'DIT'];
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
  <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 0.5rem;">
    ${book.complete_book_title || 'Untitled'}
  </div>
  <div style="margin-bottom: 0.3rem;">
    <strong>Author:</strong> ${book.author || 'Unknown'}
  </div>
  <div style="margin-bottom: 0.3rem;">
    <strong>Course:</strong> ${book.course || 'N/A'}
  </div>
  <div>
    <strong>Year:</strong> ${book.year || 'N/A'}
  </div>
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

 function openUnreturnedModal() {
    document.getElementById('unreturnedModal').classList.remove('hidden');
  }

  // Function to close the modal
  function closeUnreturnedModal() {
    document.getElementById('unreturnedModal').classList.add('hidden');
  }
    // Function to confirm and clear unreturned book status
  function clearUnreturnedBooks() {
  fetch('../Controllers/ClearScannedAtController.php', {
    method: 'POST'
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showConfirmationModal('Unreturned books have been highlighted in red.', () => {
        location.reload(); // Reload after user acknowledges
      });
    } else {
      alert('Failed: ' + data.error);
    }
  })
  .catch(() => alert('Request failed.'));

  closeUnreturnedModal(); // Close any existing modal
}
function showConfirmationModal(message, onConfirm) {
  const modal = document.createElement('div');
  modal.className = 'confirmation-modal';
  modal.innerHTML = `
    <div class="modal-content">
      <div class="modal-icon animate-spin-stop">
        <svg class="check-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12" />
        </svg>
      </div>
      <p>${message}</p>
      <button id="confirm-ok" class="pulse-button">OK</button>
    </div>
  `;

  document.body.appendChild(modal);

  // Remove spinning after 1s to simulate "stop" on check
  setTimeout(() => {
    modal.querySelector('.modal-icon').classList.remove('animate-spin-stop');
  }, 1000);

  document.getElementById('confirm-ok').addEventListener('click', () => {
    modal.remove();
    if (onConfirm) onConfirm();
  });
}
  </script>
</body>
</html>
