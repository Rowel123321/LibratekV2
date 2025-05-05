<?php
session_start();
require_once 'DbController.php';

header('Content-Type: application/json');

// 🔴 Handle logout
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    echo json_encode(["status" => "success", "message" => "Logged out successfully."]);
    exit;
}

// 🔐 Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// 🟢 Register
if (isset($data['name'], $data['email'], $data['password'])) {
    $name = trim($data['name']);
    $email = strtolower(trim($data['email']));
    $password = $data['password'];

    // Check if email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(["status" => "error", "message" => "Email already registered."]);
        exit;
    }

    // Register new user with default role 'staff'
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'staff')");
    $stmt->execute([$name, $email, $hashedPassword]);

    echo json_encode(["status" => "success", "message" => "Account created successfully."]);
    exit;
}

// 🔐 Login
if (isset($data['email'], $data['password'])) {
    $email = strtolower(trim($data['email']));
    $password = $data['password'];

    $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role']; // ✅ Save user role

        echo json_encode(["status" => "success", "message" => "Login successful", "name" => $user['name']]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
    }
    exit;
}

// ⚠️ Catch-all
echo json_encode(["status" => "error", "message" => "Missing required fields."]);
