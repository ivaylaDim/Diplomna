<?php
session_start();
require_once '../db.php';


$content_id = isset($_GET['content_id']) ? (int)$_GET['content_id'] : (isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0);
if (!$content_id) { http_response_code(400); echo json_encode(['status'=>'error','message'=>'Невалидно ID']); exit; }

$stmt = $conn->prepare("SELECT * FROM contents WHERE id = ? LIMIT 1");
if (!$stmt) { http_response_code(500); echo json_encode(['status'=>'error','message'=>'DB error']); exit; }
$stmt->bind_param('i',$content_id); $stmt->execute(); $res = $stmt->get_result(); $content = $res->fetch_assoc();
if (!$content) { http_response_code(404); echo json_encode(['status'=>'error','message'=>'Content not found']); exit; }

$type = $content['type'] ?? '';
$typeRow = null;
switch ($type) {
    case 'book':
        $q = "SELECT * FROM books WHERE content_id = ? LIMIT 1";
        break;
    case 'film':
        $q = "SELECT * FROM films WHERE content_id = ? LIMIT 1";
        break;
    case 'tv':
        $q = "SELECT * FROM tv_shows WHERE content_id = ? LIMIT 1";
        break;
    case 'article':
        $q = "SELECT * FROM articles WHERE content_id = ? LIMIT 1";
        break;
    default:
        $q = null;
}

if ($q) {
    $s = $conn->prepare($q);
    if ($s) {
        $s->bind_param('i',$content_id);
        $s->execute();
        $r = $s->get_result();
        $typeRow = $r->fetch_assoc() ?: null;
    }
}

echo json_encode(['status'=>'success','data'=>['content'=>$content,'typeRow'=>$typeRow]]);
exit;

?>
