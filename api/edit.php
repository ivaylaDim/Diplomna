<?php
session_start();

require_once __DIR__ . '/../db.php';
header('Content-Type: application/json; charset=utf-8');

// basic auth
if (!isset($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(['status' => 'error', 'message' => 'Моля, влезте в профила си.']);
	exit;
}

$current_user = (int)($_SESSION['user_id']);
$current_role = $_SESSION['role'] ?? 'user';

$content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
if (!$content_id) {
	echo json_encode(['status' => 'error', 'message' => 'Невалидно ID.']);
	exit;
}

// load content
$s = $conn->prepare("SELECT id, user_id, type, cover_path FROM contents WHERE id = ? LIMIT 1");
if (!$s) { echo json_encode(['status'=>'error','message'=>'DB error: '.$conn->error]); exit; }
$s->bind_param('i',$content_id);
$s->execute();
$r = $s->get_result();
$content = $r->fetch_assoc();
if (!$content) { echo json_encode(['status' => 'error', 'message' => 'Съдържанието не е намерено.']); exit; }

// authorization: owner or moderator/administrator
if ($content['user_id'] != $current_user && !in_array($current_role, ['moderator','administrator'])) {
	http_response_code(403);
	echo json_encode(['status' => 'error', 'message' => 'Нямате право да редактирате този материал.']);
	exit;
}

$type = $content['type'];

// gather common fields
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$year = (isset($_POST['year']) && $_POST['year'] !== '') ? (int)$_POST['year'] : null;
$genre = trim($_POST['genre'] ?? '');

// handle optional cover upload
$new_cover = null;
if (isset($_FILES['cover_path']) && isset($_FILES['cover_path']['tmp_name']) && $_FILES['cover_path']['size'] > 0) {
	$upload_dir = __DIR__ . '/../assets/img/uploads/';
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
	// update contents table
	if ($new_cover) {
		$upd = $conn->prepare("UPDATE contents SET title = ?, description = ?, year = ?, genre = ?, cover_path = ? WHERE id = ?");
		if (!$upd) throw new Exception($conn->error);
		$upd->bind_param('ssissi', $title, $description, $year, $genre, $new_cover, $content_id);
	} else {
		$upd = $conn->prepare("UPDATE contents SET title = ?, description = ?, year = ?, genre = ? WHERE id = ?");
		if (!$upd) throw new Exception($conn->error);
		$upd->bind_param('ssisi', $title, $description, $year, $genre, $content_id);
	}
	if (!$upd->execute()) throw new Exception('Грешка при обновяване на съдържанието.');

	// update type-specific table
	switch ($type) {
		case 'book':
			$author = trim($_POST['book_author'] ?? '');
			// either update or insert
			$st = $conn->prepare("SELECT content_id FROM books WHERE content_id = ? LIMIT 1");
			$st->bind_param('i',$content_id);
			$st->execute();
			$res = $st->get_result();
			if ($res->fetch_assoc()) {
				$q = $conn->prepare("UPDATE books SET author = ? WHERE content_id = ?");
				$q->bind_param('si', $author, $content_id);
				if (!$q->execute()) throw new Exception('Грешка при обновяване на данни за книга.');
			} else {
				$q = $conn->prepare("INSERT INTO books (content_id, author) VALUES (?, ?)");
				$q->bind_param('is', $content_id, $author);
				if (!$q->execute()) throw new Exception('Грешка при добавяне на данни за книга.');
			}
			break;
		case 'film':
			$director = trim($_POST['film_director'] ?? '');
			$actors = trim($_POST['film_actors'] ?? '');
			$st = $conn->prepare("SELECT content_id FROM films WHERE content_id = ? LIMIT 1");
			$st->bind_param('i',$content_id);
			$st->execute();
			$res = $st->get_result();
			if ($res->fetch_assoc()) {
				$q = $conn->prepare("UPDATE films SET director = ?, actors = ? WHERE content_id = ?");
				$q->bind_param('ssi', $director, $actors, $content_id);
				if (!$q->execute()) throw new Exception('Грешка при обновяване на данни за филм.');
			} else {
				$q = $conn->prepare("INSERT INTO films (content_id, director, actors) VALUES (?, ?, ?)");
				$q->bind_param('iss', $content_id, $director, $actors);
				if (!$q->execute()) throw new Exception('Грешка при добавяне на данни за филм.');
			}
			break;
		case 'tv':
			$showrunner = trim($_POST['tv_showrunner'] ?? '');
			$seasons = (isset($_POST['tv_seasons']) && $_POST['tv_seasons'] !== '') ? (int)$_POST['tv_seasons'] : null;
			$episodes = (isset($_POST['tv_episodes']) && $_POST['tv_episodes'] !== '') ? (int)$_POST['tv_episodes'] : null;
			$st = $conn->prepare("SELECT content_id FROM tv_shows WHERE content_id = ? LIMIT 1");
			$st->bind_param('i',$content_id);
			$st->execute();
			$res = $st->get_result();
			if ($res->fetch_assoc()) {
				$q = $conn->prepare("UPDATE tv_shows SET showrunner = ?, seasons = ?, episodes = ? WHERE content_id = ?");
				$q->bind_param('siii', $showrunner, $seasons, $episodes, $content_id);
				if (!$q->execute()) throw new Exception('Грешка при обновяване на данни за ТВ.');
			} else {
				$q = $conn->prepare("INSERT INTO tv_shows (content_id, showrunner, seasons, episodes) VALUES (?, ?, ?, ?)");
				$q->bind_param('issi', $content_id, $showrunner, $seasons, $episodes);
				if (!$q->execute()) throw new Exception('Грешка при добавяне на данни за ТВ.');
			}
			break;
		case 'article':
			$author = trim($_POST['article_author'] ?? '');
			$publication = trim($_POST['article_publication'] ?? '');
			$published_date = trim($_POST['article_published_date'] ?? '') ?: null;
			$st = $conn->prepare("SELECT content_id FROM articles WHERE content_id = ? LIMIT 1");
			$st->bind_param('i',$content_id);
			$st->execute();
			$res = $st->get_result();
			if ($res->fetch_assoc()) {
				$q = $conn->prepare("UPDATE articles SET author = ?, publication = ?, published_date = ? WHERE content_id = ?");
				$q->bind_param('sssi', $author, $publication, $published_date, $content_id);
				if (!$q->execute()) throw new Exception('Грешка при обновяване на данни за статия.');
			} else {
				$q = $conn->prepare("INSERT INTO articles (content_id, author, publication, published_date) VALUES (?, ?, ?, ?)");
				$q->bind_param('isss', $content_id, $author, $publication, $published_date);
				if (!$q->execute()) throw new Exception('Грешка при добавяне на данни за статия.');
			}
			break;
		default:
			// no extra table
			break;
	}

	// remove old cover if replaced
	if ($new_cover && !empty($content['cover_path'])) {
		$old = __DIR__ . '/../' . ltrim($content['cover_path'], '/\\');
		if (file_exists($old)) @unlink($old);
	}

	$conn->commit();
	echo json_encode(['status'=>'success','message'=>'Съдържанието беше актуализирано.']);
	exit;

} catch (Exception $e) {
	$conn->rollback();
	echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
	exit;
}

?>
