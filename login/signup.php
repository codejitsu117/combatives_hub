<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first'] ?? '');
    $last = trim($_POST['last'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $confirmEmail = trim($_POST['confirm-email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm-password'] ?? '';

    // Validation
    if ($email !== $confirmEmail || $password !== $confirmPassword) {
        $error = "Email or password do not match";
    } elseif (!$first || !$last || !$email || !$password) {
        $error = "All fields are required";
    } else {
        // Password complexity check
        $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{15,}$/';
        if (!preg_match($passwordPattern, $password)) {
            $error = "Password must be at least 15 characters with upper and lower case letters, a number, and a special character.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = "Email already registered";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$first, $last, $email, $hashed]);
                    header("Location: landing.html?success=Account created. Please login.");
                    exit;
                }
            } catch (PDOException $e) {
                $error = "Signup failed. Please try again.";
            }
        }
    }
}
