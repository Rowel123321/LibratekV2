<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account - Libratek</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Days+One&family=Great+Vibes&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body, html { height: 100%; font-family: 'Arial', sans-serif; }

    .container { display: flex; height: 100vh; }

    .left-section {
      flex: 1;
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../Images/bg-libratek.avif') no-repeat center center;
      background-size: cover;
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

    .left-section hr {
      width: 80px;
      border: 1px solid white;
      margin: 1rem auto;
    }

    .left-section p {
      font-size: 1rem;
      max-width: 400px;
    }

    .right-section {
      flex: 1;
      background: #0a0a0a;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
      color: white;
    }

    .form-container {
      width: 100%;
      max-width: 400px;
    }

    .form-container img {
      width: 150px;
      display: block;
      margin: 0 auto 1rem;
    }

    .form-container h2 {
      text-align: center;
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }

    .form-container p {
      text-align: center;
      font-size: 0.9rem;
      color: #ccc;
      margin-bottom: 2rem;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      font-size: 0.9rem;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: none;
      border-radius: 6px;
    }

    button {
      width: 100%;
      padding: 12px;
      background-color: white;
      color: black;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #f1f1f1;
    }

    .form-footer {
      text-align: center;
      font-size: 0.85rem;
      color: #ccc;
      margin-top: 1rem;
    }

    .form-footer a {
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
      color: #27ae60; /* ✅ Green success check */
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
    <div class="left-section">
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
        Join us today! Discover a smarter way to track, locate, and organize your library resources.
      </p>
    </div>

    <div class="right-section">
      <div class="form-container">
        <img src="../Images/logo.png" alt="Libratek Logo">
        <h2>Create an account</h2>
        <p>Enter your details below to create your account</p>
        <form id="registerForm">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" placeholder="Full name" required>

          <label for="email">Email address</label>
          <input type="email" id="email" name="email" placeholder="email@example.com" required>

          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Password" required>

          <label for="confirm-password">Confirm password</label>
          <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm password" required>

          <button type="submit">Create account</button>
        </form>
        <div class="form-footer">
          Already have an account? <a href="login.php">Log in</a>
        </div>
      </div>
    </div>
  </div>
  <!-- Register Success Modal -->
  <div id="registerModal" class="modal hidden">
    <div class="modal-content">
      <i class="fas fa-check-circle modal-icon success"></i>
      <h3>Account Created!</h3>
    </div>
  </div>
  <script>
    // Logo/text swap animation
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

    // Form submission logic
    document.getElementById('registerForm').addEventListener('submit', function (e) {
      e.preventDefault();

      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirm = document.getElementById('confirm-password').value;

      if (password !== confirm) {
        alert('Passwords do not match.');
        return;
      }

      fetch('../Controllers/UserController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, password })
      })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            showRegisterModal();
          } else {
            alert(data.message || 'Registration failed.');
          }
        })
        .catch(err => {
          console.error(err);
          alert('An error occurred. Please try again.');
        });
    });
    function showRegisterModal() {
      const modal = document.getElementById('registerModal');
      modal.classList.remove('hidden');

      setTimeout(() => {
        window.location.href = 'login.php'; // redirect to login after success
      }, 1500);
    }
  </script>
</body>
</html>
