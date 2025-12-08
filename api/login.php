<?php
session_start();
require_once "../db.php";

// detect AJAX / JSON-accepting clients ?? idk wat this does
$acceptsJson = false;
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $acceptsJson = true;
} elseif (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    $acceptsJson = true;
}


$usernameOrEmail = trim($_POST['log-emailOrUsername'] ?? '');
$password = $_POST['log-password'] ?? '';
$remember = isset($_POST['log-remember']) ? (int)$_POST['log-remember'] : 0;

if (empty($usernameOrEmail) || empty($password)) {
    if ($acceptsJson) {
        echo json_encode(['status' => 'error', 'message' => 'Моля, попълнете всички полета!']);
        exit;
    }
    $_SESSION['flash_error'] = 'Грешка';
    header('Location: ../log_in.php');
    exit;
}

// // Accept token from common names used by clients
// $check_recaptcha = $_POST['recaptcha'] ?? $_POST['g-recaptcha-response'] ?? '';
// if (empty($check_recaptcha)) {
//     echo json_encode(['status' => 'error', 'message' => 'recaptcha липсва. Моля, опитайте отново.']);
//     exit;
// }

// $secretKey = defined('RECAPTCHA_SECRET') ? RECAPTCHA_SECRET : (getenv('RECAPTCHA_SECRET') ?: '');
// $verifyUrl = "https://www.google.com/recaptcha/api/siteverify?secret=" . urlencode($secretKey) . "&response=" . urlencode($check_recaptcha);
// $verify = file_get_contents($verifyUrl);
// $response = json_decode($verify);
// if (empty($response) || !$response->success) {
//     $errors = $response->{"error-codes"} ?? [];
//     echo json_encode(['status' => 'error', 'message' => 'Моля потвърдете, че не сте робот!', 'errors' => $errors]);
//     exit;
// }


$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();
if ($user = $result->fetch_assoc()) {
   
    if (password_verify($password, $user["hashed_pass"])) {
        $_SESSION['username'] = $user["username"];
        $_SESSION['user_id'] = $user["id"];
        // Store user role in session for access control
        $_SESSION['role'] = $user['role'] ?? 'user';
        if ($remember) {
            
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + (60 * 60 * 24 * 30), $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        if ($acceptsJson) {
            echo json_encode(['status' => 'success', 'message' => 'Успешен вход!']);
            exit;
        }
        header('Location: ../index.php');
        exit;
    } else {
        if ($acceptsJson) {
            echo json_encode(['status' => 'error', 'message' => 'Грешни данни за вход!']);
            exit;
        }
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Грешни данни за вход!']);
    exit;
}
