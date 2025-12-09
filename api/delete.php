<?php
session_start();

require_once "../db.php";

header('Content-Type: application/json; charset=utf-8');

// get content id
$content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
if (!$content_id) {
    echo json_encode(['status' => 'error', 'message' => 'Невалидно ID на съдържанието.']);
    exit;
}

$current_user = $_SESSION['user_id'];
$current_role = $_SESSION['role'] ?? 'user'; //if role not set, fall to user


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


$type = $content['type'];


$conn->begin_transaction();
try {
    // delete by content type
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
            // else delete from contents only
            break;
    }

    // delete from contents
    $delc = $conn->prepare("DELETE FROM contents WHERE id = ?");
    $delc->bind_param('i', $content_id);
    if (!$delc->execute()) {
        throw new Exception('Грешка при изтриване на съдържанието.');
    }

    // remove cover file if exists (img stored on server in assets folder)
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
