<?php
require_once "../db.php";


$username = trim($_POST["reg-username"] ?? "");
$name = trim($_POST["reg-name"] ?? "");
$email = trim($_POST["reg-email"] ?? "");
$password  = $_POST["reg-password"] ?? "";
$passwordRepeat = $_POST["reg-password-repeat"] ?? "";
$user_role = $_POST["reg-role"] ?? "";

if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
    echo json_encode(['status' => 'error', 'message' => 'Моля, попълнете всички полета!']);
    exit;
}

if ($password != $passwordRepeat) {
    echo json_encode(['status' => 'error', 'message' => 'Двете пароли не съвпадат!']);
    exit;
}

// $check_recaptcha = $_POST["recaptcha"];
// $secretKey = "6LdffggsAAAAAIIcNGT12BkOJoo1qIHefQfYIAfy";
// $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$check_recaptcha}");
// $response = json_decode($verify);
// if (!$response->success) {
//     echo json_encode(['status' => 'error', 'message' => 'Моля потвърдете, че не сте робот!']);
//     exit;
// }


$check_existing_user = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
$stmt = $conn->prepare($check_existing_user);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Съществува потребител с това потребителско име или с този имейл!']);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$roleMap = [
    'user' => 'user',
    'moderator' => 'moderator',
    'admin' => 'administrator'
];

$role = $roleMap[$user_role] ?? 'user'; // default to user role
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$new_user_query = "INSERT INTO users (username, email, hashed_pass, full_name, role) VALUES (?, ?, ?, ?, ?)";


$stmt = $conn->prepare($new_user_query);
$stmt->bind_param("sssss", $username, $email, $passwordHash, $name, $role);


if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Успешна регистрация!']);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Грешка при регистрация!']);
    exit;
}
