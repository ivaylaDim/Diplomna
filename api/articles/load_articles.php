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
                    a.author AS article_author,
                    a.publication AS publication,
                    a.published_date AS published_date,
                    u.username AS username
                FROM contents c
                INNER JOIN articles a ON a.content_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.type = 'article' AND (c.title LIKE ? OR a.author LIKE ? OR a.publication LIKE ? OR u.username LIKE ?)
                ORDER BY c.date_uploaded DESC
                LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param('ssssii', $like, $like, $like, $like, $limit, $offset);
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
                    a.author AS article_author,
                    a.publication AS publication,
                    a.published_date AS published_date,
                    u.username AS username
                FROM contents c
                INNER JOIN articles a ON a.content_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.type = 'article'
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
        // format published_date to same string if not null
        if (!empty($row['published_date'])) {
            $row['published_date'] = $row['published_date'];
        } else {
            $row['published_date'] = '';
        }
        $rows[] = $row;
    }

    if ($format === 'json' || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'data' => $rows]);
        exit;
    }

    // Render HTML cards similar to films loader
    foreach ($rows as $art) {
        $cover = htmlspecialchars($art['cover_path'] ?? '', ENT_QUOTES);
        $title = htmlspecialchars($art['title'] ?? '', ENT_QUOTES);
        $desc = htmlspecialchars($art['description'] ?? '', ENT_QUOTES);
        $author = htmlspecialchars($art['article_author'] ?? '', ENT_QUOTES);
        $publication = htmlspecialchars($art['publication'] ?? '', ENT_QUOTES);
        $published_date = htmlspecialchars($art['published_date'] ?? '', ENT_QUOTES);
        $username = htmlspecialchars($art['username'] ?? 'unknown', ENT_QUOTES);
        $date = htmlspecialchars($art['date_uploaded'] ?? '', ENT_QUOTES);

        echo "<div class='article-card'>";
        if ($cover) {
            echo "<img src='" . $cover . "' alt='" . $title . "' />";
        }
        echo "<div class='article-info'>";
        echo "<h3>" . $title . "</h3>";
        echo "<p><b>Автор:</b> " . $author . "</p>";
        echo "<p><b>Издание:</b> " . $publication . "</p>";
        if ($published_date) {
            echo "<p><b>Дата:</b> " . $published_date . "</p>";
        }
        echo "<p>" . $desc . "</p>";
        echo "<p class='small'><b>Публикувал:</b> " . $username . " — " . $date . "</p>";
        echo "<div class='article-actions'>";
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $art['user_id']) {
            echo "<form class='edit-article-form' method='POST' action='api/articles/edit.php' style='display:inline;margin-right:6px;'>";
            echo "<input type='hidden' name='content_id' value='" . htmlspecialchars($art['content_id'], ENT_QUOTES) . "' />";
            echo "<button type='submit'>Редактирай</button>";
            echo "</form>";

            echo "<form class='delete-article-form' method='POST' action='api/articles/delete.php' style='display:inline;' onsubmit=\"return confirm('Наистина ли искате да изтриете този материал?');\">";
            echo "<input type='hidden' name='content_id' value='" . htmlspecialchars($art['content_id'], ENT_QUOTES) . "' />";
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
