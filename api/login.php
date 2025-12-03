<?php
session_start();
require_once "../db.php";

$usernameOrEmail = $_POST["usernameOrEmail"] ?? "";
$password  = $_POST["password"] ?? "";
$remember = isset($_POST["remember"]) ? (int)$_POST["remember"] : 0;

if (empty($usernameOrEmail) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Моля, попълнете всички полета!']);
    exit;
}

$check_recaptcha = $_POST["recaptcha"];
$secretKey = "6LdsWCAsAAAAAFOwdRopAYv8aaB2we0trMTpr5jj";
$verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$check_recaptcha}");
$response = json_decode($verify);
if (!$response->success) {
    echo json_encode(['status' => 'error', 'message' => 'Моля потвърдете, че не сте робот!']);
    exit;
}


$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
$stmt->execute();
$result = $stmt->get_result();
if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user["password"])) {
        $_SESSION['username'] = $user["username"];
        $_SESSION['user_id'] = $user["id"];
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->bind_param("si", $token, $user["id"]);
            $stmt->execute();
            setcookie("remember", $token, time() + (60 * 60 * 24 * 30), "/", false, true);
        }
        echo json_encode(['status' => 'success', 'message' => 'Добре дошъл!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Грешни данни за вход!']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Грешни данни за вход!']);
    exit;
}
