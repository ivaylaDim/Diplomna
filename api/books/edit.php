<?php
session_start();
require_once __DIR__ . '/../../db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Моля, влезте в профила си.']);
    exit;
}

$current_user = (int)$_SESSION['user_id'];
$current_role = $_SESSION['role'] ?? 'user';

$content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
if (!$content_id) {
    echo json_encode(['status' => 'error', 'message' => 'Невалидно ID.']);
    exit;
}

// Load content and verify type
$stmt = $conn->prepare("SELECT id, user_id, type, cover_path FROM contents WHERE id = ? LIMIT 1");
if (!$stmt) { echo json_encode(['status'=>'error','message'=>'DB error']); exit; }
$stmt->bind_param('i', $content_id);
$stmt->execute();
$res = $stmt->get_result();
$content = $res->fetch_assoc();
if (!$content) { echo json_encode(['status'=>'error','message'=>'Съдържанието не е намерено.']); exit; }
if ($content['type'] !== 'book') { echo json_encode(['status'=>'error','message'=>'Неправилен тип съдържание.']); exit; }

// Authorization
if ($content['user_id'] != $current_user && !in_array($current_role, ['moderator','administrator'])) {
    http_response_code(403);
    echo json_encode(['status'=>'error','message'=>'Нямате право да редактирате този материал.']);
    exit;
}

// Gather fields
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$year = isset($_POST['year']) && $_POST['year'] !== '' ? (int)$_POST['year'] : NULL;
$genre = trim($_POST['genre'] ?? '');
$author = trim($_POST['book_author'] ?? '');

// Optional cover upload
$new_cover = null;
if (isset($_FILES['cover_path']) && $_FILES['cover_path']['size'] > 0) {
    $upload_dir = __DIR__ . '/../../assets/img/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $file_tmp = $_FILES['cover_path']['tmp_name'];
    $file_name = strtolower(pathinfo($_FILES['cover_path']['name'], PATHINFO_BASENAME));
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $allowed)) {
        echo json_encode(['status'=>'error','message'=>'Невалиден формат за снимка.']); exit;
    }
    $unique = uniqid() . '.' . $ext;
    if (!move_uploaded_file($file_tmp, $upload_dir . $unique)) {
        echo json_encode(['status'=>'error','message'=>'Грешка при качване на снимката.']); exit;
    }
    $new_cover = 'assets/img/uploads/' . $unique;
}

$conn->begin_transaction();
try {
    // Update contents
    $upd = $conn->prepare("UPDATE contents SET title = ?, description = ?, year = ?, genre = ?" . ($new_cover ? ", cover_path = ?" : "") . " WHERE id = ?");
    if (!$upd) throw new Exception($conn->error);
    if ($new_cover) $upd->bind_param('ssis si', $title, $description, $year, $genre, $new_cover, $content_id);
    else $upd->bind_param('ssis i', $title, $description, $year, $genre, $content_id);
    // Note: bind_param above uses types but to avoid errors we'll build accordingly below
    if ($new_cover) {
        $upd = $conn->prepare("UPDATE contents SET title = ?, description = ?, year = ?, genre = ?, cover_path = ? WHERE id = ?");
        $upd->bind_param('ssissi', $title, $description, $year, $genre, $new_cover, $content_id);
    } else {
        $upd = $conn->prepare("UPDATE contents SET title = ?, description = ?, year = ?, genre = ? WHERE id = ?");
        $upd->bind_param('ssisi', $title, $description, $year, $genre, $content_id);
    }
    if (!$upd->execute()) throw new Exception('Грешка при обновяване на съдържанието.');

    // Update books table
    $stmtb = $conn->prepare("SELECT content_id FROM books WHERE content_id = ? LIMIT 1");
    $stmtb->bind_param('i', $content_id);
    $stmtb->execute();
    $rb = $stmtb->get_result();
    if ($rb->fetch_assoc()) {
        $upb = $conn->prepare("UPDATE books SET author = ? WHERE content_id = ?");
        $upb->bind_param('si', $author, $content_id);
        if (!$upb->execute()) throw new Exception('Грешка при обновяване на данни за книга.');
    } else {
        $insb = $conn->prepare("INSERT INTO books (content_id, author) VALUES (?, ?)");
        $insb->bind_param('is', $content_id, $author);
        if (!$insb->execute()) throw new Exception('Грешка при добавяне на данни за книга.');
    }

    // If new cover uploaded, remove old
    if ($new_cover && !empty($content['cover_path'])) {
        $old = __DIR__ . '/../../' . ltrim($content['cover_path'], '/\\');
        if (file_exists($old)) @unlink($old);
    }

    $conn->commit();
    echo json_encode(['status'=>'success','message'=>'Книгата беше актуализирана.']);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    exit;
}

?>
