<?php
session_start();
require_once "../db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Моля, влезте в профила си, за да подадете материал.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$type = trim($_POST['type'] ?? '');
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$year = isset($_POST['year']) && $_POST['year'] !== '' ? (int)$_POST['year'] : NULL;
$genre = trim($_POST['genre'] ?? '');

// Validate common fields
if (empty($type) || empty($title) || empty($description)) {
    echo json_encode(['status' => 'error', 'message' => 'Моля, попълнете всички задължителни полета.']);
    exit;
}

// Validate content type
$valid_types = ['book', 'film', 'tv', 'article'];
if (!in_array($type, $valid_types)) {
    echo json_encode(['status' => 'error', 'message' => 'Невалиден тип на съдържанието.']);
    exit;
}

// Handle file upload (optional)
$cover_path = NULL;
if (isset($_FILES['cover_path']) && $_FILES['cover_path']['size'] > 0) {
    $upload_dir = '../assets/img/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_tmp = $_FILES['cover_path']['tmp_name'];
    $file_name = $_FILES['cover_path']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Whitelist image extensions
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($file_ext, $allowed_exts)) {
        echo json_encode(['status' => 'error', 'message' => 'Само снимки са позволени. Позволени формати: jpg, png, gif, webp']);
        exit;
    }
    
    // Generate unique filename
    $file_name = uniqid() . '.' . $file_ext;
    $cover_path = 'assets/img/uploads/' . $file_name;
    
    if (!move_uploaded_file($file_tmp, '../' . $cover_path)) {
        echo json_encode(['status' => 'error', 'message' => 'Грешка при качване на снимката.']);
        exit;
    }
}

// Insert into contents table
$insert_content = "INSERT INTO contents (type, title, description, year, genre, cover_path, user_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_content);
if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Грешка на базата данни: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sssisss", $type, $title, $description, $year, $genre, $cover_path, $user_id);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Грешка при вмъкване на материала.']);
    exit;
}

$content_id = $stmt->insert_id;

// Insert type-specific data
switch ($type) {
    case 'book':
        $author = trim($_POST['book_author'] ?? '');
        if (!empty($author)) {
            $insert_book = "INSERT INTO books (content_id, author) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_book);
            if (!$stmt) {
                // Rollback content insert
                $conn->query("DELETE FROM contents WHERE id = $content_id");
                echo json_encode(['status' => 'error', 'message' => 'Грешка при добавяне на данни за книга.']);
                exit;
            }
            $stmt->bind_param("is", $content_id, $author);
            if (!$stmt->execute()) {
                $conn->query("DELETE FROM contents WHERE id = $content_id");
                echo json_encode(['status' => 'error', 'message' => 'Грешка при добавяне на данни за книга.']);
                exit;
            }
        }
        break;

    case 'film':
        $director = trim($_POST['film_director'] ?? '');
        $actors = trim($_POST['film_actors'] ?? '');
        if (!empty($director) || !empty($actors)) {
            $insert_film = "INSERT INTO films (content_id, director, actors) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_film);
            if (!$stmt) {
                $conn->query("DELETE FROM contents WHERE id = $content_id");
                echo json_encode(['status' => 'error', 'message' => 'Грешка при добавяне на данни за филм.']);
                exit;
            }
            $stmt->bind_param("iss", $content_id, $director, $actors);
            if (!$stmt->execute()) {
                $conn->query("DELETE FROM contents WHERE id = $content_id");
                echo json_encode(['status' => 'error', 'message' => 'Грешка при добавяне на данни за филм.']);
                exit;
            }
        }
        break;

    case 'tv':
        $showrunner = trim($_POST['tv_showrunner'] ?? '');
        $seasons = isset($_POST['tv_seasons']) && $_POST['tv_seasons'] !== '' ? (int)$_POST['tv_seasons'] : NULL;
        $episodes = isset($_POST['tv_episodes']) && $_POST['tv_episodes'] !== '' ? (int)$_POST['tv_episodes'] : NULL;
        if (!empty($showrunner) || $seasons !== NULL || $episodes !== NULL) {
            $insert_tv = "INSERT INTO tv_shows (content_id, showrunner, seasons, episodes) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_tv);
            if (!$stmt) {
                $conn->query("DELETE FROM contents WHERE id = $content_id");
                echo json_encode(['status' => 'error', 'message' => 'Грешка при добавяне на данни за сериал.']);
                exit;
            }
            $stmt->bind_param("isii", $content_id, $showrunner, $seasons, $episodes);
            if (!$stmt->execute()) {
                $conn->query("DELETE FROM contents WHERE id = $content_id");
                echo json_encode(['status' => 'error', 'message' => 'Грешка при добавяне на данни за сериал.']);
                exit;
            }
        }
        break;

    case 'article':
        $author = trim($_POST['article_author'] ?? '');
        $publication = trim($_POST['article_publication'] ?? '');
        $published_date = trim($_POST['article_published_date'] ?? '');
        if (!empty($author) || !empty($publication) || !empty($published_date)) {
            $insert_article = "INSERT INTO articles (content_id, author, publication, published_date) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_article);
            if (!$stmt) {
                $conn->query("DELETE FROM contents WHERE id = $content_id");
                echo json_encode(['status' => 'error', 'message' => 'Грешка при добавяне на данни за статия.']);
                exit;
            }
            $stmt->bind_param("isss", $content_id, $author, $publication, $published_date);
            if (!$stmt->execute()) {
                $conn->query("DELETE FROM contents WHERE id = $content_id");
                echo json_encode(['status' => 'error', 'message' => 'Грешка при добавяне на данни за статия.']);
                exit;
            }
        }
        break;
}

echo json_encode(['status' => 'success', 'message' => 'Материалът е успешно подаден.']);
exit;

#TODO move switch case cases to separate files for better organization

?>

