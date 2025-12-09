<?php
session_start();

require_once "../db.php";

header('Content-Type: application/json; charset=utf-8');

// Require content id
$content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
if (!$content_id) {
    echo json_encode(['status' => 'error', 'message' => 'Невалидно ID на съдържанието.']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Моля, влезте в профила си.']);
    exit;
}

$current_user = (int)$_SESSION['user_id'];
$current_role = $_SESSION['role'] ?? 'user';

// Load content row to check ownership and type
$stmt = $conn->prepare("SELECT id, user_id, type, cover_path FROM contents WHERE id = ? LIMIT 1");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'DB error: ' . $conn->error]);
    exit;
}
$stmt->bind_param('i', $content_id);
$stmt->execute();
$res = $stmt->get_result();
$content = $res->fetch_assoc();
if (!$content) {
    echo json_encode(['status' => 'error', 'message' => 'Съдържанието не е намерено.']);
    exit;
}

// Authorization: owner or moderator/administrator
if ($content['user_id'] != $current_user && !in_array($current_role, ['moderator', 'administrator'])) {
    echo json_encode(['status' => 'error', 'message' => 'Нямате право да изтривате този материал.']);
    exit;
}

$type = $content['type'];

// Start transaction
$conn->begin_transaction();
try {
    // Delete type-specific row first
    switch ($type) {
        case 'book':
            $del = $conn->prepare("DELETE FROM books WHERE content_id = ?");
            $del->bind_param('i', $content_id);
            $del->execute();
            break;
        case 'film':
            $del = $conn->prepare("DELETE FROM films WHERE content_id = ?");
            $del->bind_param('i', $content_id);
            $del->execute();
            break;
        case 'tv':
            $del = $conn->prepare("DELETE FROM tv_shows WHERE content_id = ?");
            $del->bind_param('i', $content_id);
            $del->execute();
            break;
        case 'article':
            $del = $conn->prepare("DELETE FROM articles WHERE content_id = ?");
            $del->bind_param('i', $content_id);
            $del->execute();
            break;
        default:
            // Unknown type - continue to delete contents row only
            break;
    }

    // Delete from contents
    $delc = $conn->prepare("DELETE FROM contents WHERE id = ?");
    $delc->bind_param('i', $content_id);
    if (!$delc->execute()) {
        throw new Exception('Грешка при изтриване на съдържанието.');
    }

    // Remove cover file if exists
    if (!empty($content['cover_path'])) {
        $filePath = __DIR__ . '/../' . ltrim($content['cover_path'], '/\\');
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Съдържанието беше изтрито.']);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

?>
