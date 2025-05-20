<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header("Location: dashboard.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Libratek Login</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Days+One&family=Great+Vibes&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body, html { height: 100%; font-family: 'Arial', sans-serif; }

    .container {
      display: flex;
      height: 100vh;
    }

    .left-panel {
      flex: 1;
      background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../Images/bg-libratek.avif') center/cover no-repeat;
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 2rem;
      overflow: hidden;
    }

    .slide-container {
      position: relative;
      height: 140px;
      width: 100%;
    }

    .slide-item {
      position: absolute;
      width: 100%;
      transition: all 0.4s ease;
      opacity: 0;
    }

    .slide-in {
      animation: slideInUp 0.6s ease forwards;
    }

    .slide-out {
      animation: slideOutUp 0.6s ease forwards;
    }

    @keyframes slideInUp {
      from { transform: translateY(100%); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @keyframes slideOutUp {
      from { transform: translateY(0); opacity: 1; }
      to { transform: translateY(-100%); opacity: 0; }
    }

    h1 {
      font-family: 'Days One', sans-serif;
      font-size: 2.5rem;
      line-height: 1.3;
    }

    .great {
      font-family: 'Great Vibes', cursive;
      font-size: 4.2rem;
    }

    .left-panel hr {
      margin: 1rem auto;
      width: 80px;
      border: 1px solid white;
    }

    .left-panel p {
      font-size: 1rem;
      max-width: 400px;
    }

    .right-panel {
      flex: 1;
      background: #0a0a0a;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      padding: 2rem;
    }

    .form-box {
      width: 100%;
      max-width: 360px;
    }

    .form-box img {
      display: block;
      margin: 0 auto 1rem;
      height: 50px;
    }

    .form-box h2 {
      text-align: center;
      margin-bottom: 0.5rem;
    }

    .form-box p.description {
      font-size: 0.875rem;
      text-align: center;
      color: #ccc;
      margin-bottom: 1.5rem;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.25rem;
      font-size: 0.875rem;
    }

    .form-group input {
      width: 100%;
      padding: 0.75rem;
      border-radius: 5px;
      border: 1px solid #ccc;
      background: #f0f0f0;
      color: #000;
    }

    .form-group.remember {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.85rem;
    }

    .form-group.remember label {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-group.remember a {
      color: #aaa;
      text-decoration: none;
    }

    .form-group.remember a:hover {
      text-decoration: underline;
    }

    button[type="submit"] {
      width: 100%;
      padding: 0.75rem;
      border: none;
      border-radius: 5px;
      background-color: white;
      color: black;
      font-weight: bold;
      cursor: pointer;
      margin-top: 1rem;
    }

    .signup {
      text-align: center;
      font-size: 0.9rem;
      margin-top: 1.5rem;
    }

    .signup a {
      color: white;
      text-decoration: underline;
    }
    .modal {
      position: fixed;
      inset: 0;
      background-color: rgba(0, 0, 0, 0.6);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 5000;
    }

    .modal.hidden {
      display: none;
    }

    .modal-content {
      background-color: var(--container-bg, #ffffff);
      color: var(--text-color, #000000);
      padding: 30px;
      border-radius: 12px;
      width: 320px;
      max-width: 90%;
      text-align: center;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
      animation: popin 0.4s ease;
    }

    .modal-icon {
      font-size: 60px;
      margin-bottom: 20px;
    }

    .success {
      color: #27ae60; /* ✅ Green for success */
    }

    @keyframes popin {
      from {
        transform: scale(0.8);
        opacity: 0;
      }
      to {
        transform: scale(1);
        opacity: 1;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <div class="slide-container">
        <div class="slide-item slide-in" id="headline">
          <h1>
            EMPOWERING <span class="great">L</span>EARNING,<br />
            SECURING KNOWLEDGE
          </h1>
        </div>
        <div class="slide-item" id="logo">
          <img src="../Images/logo1.png" alt="Libratek Logo" style="width: 200px; height: auto;" />
        </div>
      </div>
      <hr />
      <p>
        Track, locate, and organize your library with our RFID-based technology — making book handling faster, easier, and secure.
      </p>
    </div>
    <!-- Login Success Modal -->
    <div id="loginModal" class="modal hidden">
      <div class="modal-content">
      <i class="fas fa-check-circle modal-icon success"></i> 
        <h3>Login Successful!</h3>
      </div>
    </div>
    <div class="right-panel">
      <div class="form-box">
        <img src="../Images/logo.png" alt="Libratek Logo">
        <h2>Log in to your account</h2>
        <p class="description">Enter your email and password below to log in</p>

        <form id="loginForm">
          <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" placeholder="email@example.com" required>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required>
          </div>

          <button type="submit">Log in</button>

          <div class="signup">
            Don't have an account? <a href="register.php">Sign up</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  const headline = document.getElementById("headline");
  const logo = document.getElementById("logo");

  let showingHeadline = true;

  setInterval(() => {
    if (showingHeadline) {
      headline.classList.remove("slide-in");
      headline.classList.add("slide-out");
      setTimeout(() => {
        headline.style.opacity = 0;
        logo.classList.remove("slide-out");
        logo.classList.add("slide-in");
        logo.style.opacity = 1;
      }, 500);
    } else {
      logo.classList.remove("slide-in");
      logo.classList.add("slide-out");
      setTimeout(() => {
        logo.style.opacity = 0;
        headline.classList.remove("slide-out");
        headline.classList.add("slide-in");
        headline.style.opacity = 1;
      }, 500);
    }
    showingHeadline = !showingHeadline;
  }, 3000);

  // ✅ LOGIN SCRIPT
  document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;

    fetch('../Controllers/UserController.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          showLoginModal(data.role); // ✅ Pass user role to modal redirect
        } else {
          alert(data.message || 'Invalid credentials.');
        }
      })
      .catch(err => {
        console.error(err);
        alert('Login error. Please try again.');
      });
  });

  // ✅ Redirect based on role
  function showLoginModal(role) {
    const modal = document.getElementById('loginModal');
    modal.classList.remove('hidden');

    setTimeout(() => {
      if (role === 'admin') {
        window.location.href = 'manage_books.php';
      } else {
        window.location.href = 'dashboard.php';
      }
    }, 1500);
  }
</script>
</body>
</html>
