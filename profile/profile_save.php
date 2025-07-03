<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once '../login/db.php'; // Adjust path if needed

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: /login.html?error=Session expired");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../profile/user_prof.html?error=Invalid request method");
    exit;
}

// Collect and sanitize inputs
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$nickname = trim($_POST['nickname'] ?? '');
$level = $_POST['level'] ?? '';
$user_role = $_POST['user_role'] ?? '';
$installations = isset($_POST['installations']) ? $_POST['installations'] : [];

if (!$first_name || !$last_name || !$email || !$level || !$user_role) {
    header("Location: ../profile/user_prof.html?error=" . urlencode("Please fill all required fields"));
    exit;
}

// Process installations array into CSV string
$installations_str = implode(',', $installations);

// Handle profile picture upload (optional)
$profilePicPath = null;
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/profile_pics/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = basename($_FILES['profile_pic']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate file extension (allow common image types)
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($fileExt, $allowedExts)) {
        header("Location: ../profile/user_prof.html?error=" . urlencode("Invalid image file type."));
        exit;
    }

    // Create unique filename
    $newFileName = uniqid('profile_', true) . '.' . $fileExt;
    $destPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
        header("Location: ../profile/user_prof.html?error=" . urlencode("Failed to upload profile picture."));
        exit;
    }

    // Store relative path for DB
    $profilePicPath = 'uploads/profile_pics/' . $newFileName;
}

try {
    // Prepare SQL update, including profile photo if uploaded
    $sql = "UPDATE users SET
        first_name = :first_name,
        last_name = :last_name,
        email = :email,
        nickname = :nickname,
        level = :level,
        role = :role,
        installations = :installations,
        profile_completed = 1";

    if ($profilePicPath !== null) {
        $sql .= ", profile_photo = :profile_photo";
    }

    $sql .= " WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':nickname', $nickname);
    $stmt->bindParam(':level', $level);
    $stmt->bindParam(':role', $user_role);
    $stmt->bindParam(':installations', $installations_str);
    $stmt->bindParam(':id', $user_id);

    if ($profilePicPath !== null) {
        $stmt->bindParam(':profile_photo', $profilePicPath);
    }

    $stmt->execute();

    // Redirect to dashboard on success
    header("Location: ../dashboard/dashboard.html");
    exit;

} catch (PDOException $e) {
    error_log("Profile save error: " . $e->getMessage());
    header("Location: ../profile/user_prof.html?error=" . urlencode("Failed to update profile."));
    exit;
}
