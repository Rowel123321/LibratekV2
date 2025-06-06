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
  <title>Libratek Landing</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Days+One&family=Great+Vibes&display=swap" rel="stylesheet">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body, html {
      height: 100%;
      font-family: 'Arial', sans-serif;
      color: white;
    }

    .landing {
      position: relative;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('../Images/bg-libratek.avif') no-repeat center bottom;
      background-size: cover;
      padding: 2rem;
    }

    .headline {
      animation: fadeUp 1.2s ease forwards;
      opacity: 0;
      transform: translateY(20px);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .headline:hover {
      transform: scale(1.02);
    }

    .highlight {
      transition: color 0.3s ease, transform 0.3s ease;
      display: inline-block;
    }

    .headline:hover .highlight-i {
      color: #1abc9c;
      transform: scale(1.2);
    }

    .headline:hover .highlight-o {
      color: #e74c3c;
      transform: scale(1.2);
    }

    .headline:hover .highlight-t {
      color: #f1c40f;
      transform: scale(3.0);
      font-weight: bold;
    }

    @keyframes fadeUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .landing h1 {
      font-size: 2.5rem;
      line-height: 1.3;
      font-family: 'Days One', sans-serif;
      font-weight: bold;
    }

    .landing h1 .great {
      font-family: 'Great Vibes', cursive;
      font-size: 2em;
    }

    .landing hr {
      margin: 1.5rem auto;
      width: 80px;
      border: 1px solid white;
      transition: all 0.3s ease;
    }

    .landing p {
      font-size: 1.1rem;
      max-width: 600px;
      margin: 0 auto;
    }

    .header {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      background: black;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      z-index: 100;
    }

    .header img {
      height: 50px;
    }

    .nav-links {
      display: flex;
      gap: 1rem;
    }

    .nav-links a {
      color: white;
      text-decoration: none;
      font-size: 0.95rem;
    }

    .nav-links a:hover {
      text-decoration: underline;
    }

    @media (min-width: 640px) {
      .landing h1 {
        font-size: 3.5rem;
      }

      .landing p {
        font-size: 1.25rem;
      }

      .landing hr {
        width: 100px;
      }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="header">
    <img src="../Images/logo.png" alt="Libratek Logo" />
    <div class="nav-links">
      <a href="register.php">Sign up</a>
      <a href="login.php">Login</a>
    </div>
  </header>

  <!-- Landing Section -->
  <section class="landing">
    <div class="headline">
      <h1>
        EMPOWER<span class="highlight highlight-i">I</span>NG 
        <span class="great">L</span>EARNING,<br />
        SECURING KN<span class="highlight highlight-o">O</span>WLEDGE
      </h1>
      <hr />
      <p>
        <span class="highlight highlight-t">T</span>rack, locate, and organize your library with the RFID-based technology — 
        making book handling faster, easier, and secure.
      </p>
    </div>
  </section>

</body>
</html>
