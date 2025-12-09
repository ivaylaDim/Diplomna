<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$user = [
    'id' => isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null,
    'username' => $_SESSION['username'] ?? null,
    'role' => $_SESSION['role'] ?? 'guest'
];

if ($user['id']) {
    echo json_encode(['status' => 'success', 'data' => $user]);
} else {
    echo json_encode(['status' => 'success', 'data' => $user]);
}

?>
