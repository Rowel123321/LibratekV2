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

<h2>📚 Book Management</h2>
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
</script>

</body>
</html>
