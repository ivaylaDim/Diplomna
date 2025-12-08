<?php
require_once __DIR__ . '/../../db.php';
session_start();

// Optional params: limit, offset, q (search), format=json for JSON
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$q = trim($_GET['q'] ?? '');
$format = isset($_GET['format']) ? strtolower($_GET['format']) : '';

// Cap limit to avoid heavy queries
if ($limit < 1) $limit = 1;
if ($limit > 200) $limit = 200;
if ($offset < 0) $offset = 0;

try {
    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql = "SELECT
                    c.id AS content_id,
                    c.type AS content_type,
                    c.title AS title,
                    c.description AS description,
                    c.cover_path AS cover_path,
                    c.download_link AS download_link,
                    c.user_id AS user_id,
                    c.date_uploaded AS date_uploaded,
                    c.year AS year,
                    c.genre AS genre,
                    b.author AS author,
                    u.username AS username
                FROM contents c
                INNER JOIN books b ON b.content_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.type = 'book' AND (c.title LIKE ? OR b.author LIKE ? OR u.username LIKE ?)
                ORDER BY c.date_uploaded DESC
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
    } else {
        $sql = "SELECT
                    c.id AS content_id,
                    c.type AS content_type,
                    c.title AS title,
                    c.description AS description,
                    c.cover_path AS cover_path,
                    c.download_link AS download_link,
                    c.user_id AS user_id,
                    c.date_uploaded AS date_uploaded,
                    c.year AS year,
                    c.genre AS genre,
                    b.author AS author,
                    u.username AS username
                FROM contents c
                INNER JOIN books b ON b.content_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.type = 'book'
                ORDER BY c.date_uploaded DESC
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }

    if ($format === 'json' || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'data' => $rows]);
        exit;
    }

    // Render HTML cards
    foreach ($rows as $book) {
        $cover = htmlspecialchars($book['cover_path'] ?? '', ENT_QUOTES);
        $title = htmlspecialchars($book['title'] ?? '', ENT_QUOTES);
        $desc = htmlspecialchars($book['description'] ?? '', ENT_QUOTES);
        $author = htmlspecialchars($book['author'] ?? '', ENT_QUOTES);
        $year = htmlspecialchars($book['year'] ?? '', ENT_QUOTES);
        $genre = htmlspecialchars($book['genre'] ?? '', ENT_QUOTES);
        $username = htmlspecialchars($book['username'] ?? 'unknown', ENT_QUOTES);
        $date = htmlspecialchars($book['date_uploaded'] ?? '', ENT_QUOTES);

        echo "<div class='book-card'>";
        if ($cover) {
            echo "<img src='" . $cover . "' alt='" . $title . "' />";
        }
        echo "<div class='book-info'>";
        echo "<h3>" . $title . "</h3>";
        echo "<p><b>Автор:</b> " . $author . "</p>";
        if ($year) echo "<p><b>Година:</b> " . $year . "</p>";
        if ($genre) echo "<p><b>Жанр:</b> " . $genre . "</p>";
        echo "<p>" . $desc . "</p>";
        echo "<p class='small'><b>Публикувал:</b> " . $username . " — " . $date . "</p>";
        echo "<div class='book-actions'>";
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $book['user_id']) {
            echo "<form class='edit-book-form' method='POST' action='api/books/edit.php' style='display:inline;margin-right:6px;'>";
            echo "<input type='hidden' name='content_id' value='" . htmlspecialchars($book['content_id'], ENT_QUOTES) . "' />";
            echo "<button type='submit'>Редактирай</button>";
            echo "</form>";

            echo "<form class='delete-book-form' method='POST' action='api/books/delete.php' style='display:inline;' onsubmit=\"return confirm('Наистина ли искате да изтриете този материал?');\">";
            echo "<input type='hidden' name='content_id' value='" . htmlspecialchars($book['content_id'], ENT_QUOTES) . "' />";
            echo "<button type='submit'>Изтрий</button>";
            echo "</form>";
        }
        echo "</div>"; // actions
        echo "</div>"; // info
        echo "</div>"; // card
    }

    exit;

} catch (Exception $e) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } else {
        echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</div>";
    }
    exit;
}
