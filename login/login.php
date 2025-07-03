<?php
// Enable error reporting during development (remove or lower in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php'; // Ensure this uses PDO

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: landing.html?error=Email and password are required");
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, password, profile_completed, nickname, user_role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nickname'] = $user['nickname'];
            $_SESSION['user_role'] = $user['user_role'];

            // Optional: regenerate session ID for security
            session_regenerate_id(true);

            // Send nickname and role to frontend via localStorage (via redirect if needed)
            echo "<script>
                localStorage.setItem('nickname', " . json_encode($user['nickname']) . ");
                localStorage.setItem('userRole', " . json_encode($user['user_role']) . ");
                window.location.href = '" . ($user['profile_completed'] ? "/dashboard/dashboard.html" : "../profile/user_prof.html") . "';
            </script>";
            exit;
        } else {
            header("Location: landing.html?error=Incorrect email or password");
            exit;
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage()); // Log internally, do not expose details
        header("Location: landing.html?error=Login failed. Please try again.");
        exit;
    }
} else {
    header("Location: landing.html?error=Invalid request method");
    exit;
}
