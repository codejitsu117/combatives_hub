<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first'] ?? '');
    $last = trim($_POST['last'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $confirmEmail = trim($_POST['confirm-email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';

    // Basic required fields check
    if (!$first || !$last || !$email || !$password) {
        header("Location: landing.html?error=All fields are required");
        exit;
    }

    // Email and password confirmation match check
    if ($email !== $confirmEmail || $password !== $confirmPassword) {
        header("Location: landing.html?error=Email or password do not match");
        exit;
    }

    // Password complexity check: min 15 chars, upper/lower, digit, special char
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{15,}$/';
    if (!preg_match($passwordPattern, $password)) {
        header("Location: landing.html?error=Password must be at least 15 characters with upper and lower case letters, a number, and a special character.");
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header("Location: landing.html?error=Email already registered");
            exit;
        }

        // Hash password securely
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$first, $last, $email, $hashed]);

        header("Location: landing.html?success=Account created. Please login.");
        exit;

    } catch (PDOException $e) {
        // Log the error in production, show generic error message here
        header("Location: landing.html?error=Signup failed");
        exit;
    }
} else {
    // Reject any non-POST requests
    header("Location: landing.html?error=Invalid request method");
    exit;
}
