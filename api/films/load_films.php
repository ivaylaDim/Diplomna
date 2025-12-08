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
    // Build SQL with join to users for uploader username
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
                    f.director AS director,
                    f.actors AS actors,
                    u.username AS username
                FROM contents c
                INNER JOIN films f ON f.content_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.type = 'film' AND (c.title LIKE ? OR f.director LIKE ? OR u.username LIKE ?)
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
                    f.director AS director,
                    f.actors AS actors,
                    u.username AS username
                FROM contents c
                INNER JOIN films f ON f.content_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.type = 'film'
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
        // normalize actors to array if stored as comma-separated
        if (isset($row['actors']) && $row['actors'] !== null) {
            $actorsRaw = trim($row['actors']);
            $row['actors'] = $actorsRaw === '' ? [] : array_map('trim', explode(',', $actorsRaw));
        } else {
            $row['actors'] = [];
        }
        $rows[] = $row;
    }

    // If JSON requested, return JSON
    if ($format === 'json' || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'success', 'data' => $rows]);
        exit;
    }

    // Otherwise render HTML similar to gallery's load_photos.php
    foreach ($rows as $film) {
        $cover = htmlspecialchars($film['cover_path'] ?? '', ENT_QUOTES);
        $title = htmlspecialchars($film['title'] ?? '', ENT_QUOTES);
        $desc = htmlspecialchars($film['description'] ?? '', ENT_QUOTES);
        $director = htmlspecialchars($film['director'] ?? '', ENT_QUOTES);
        $year = htmlspecialchars($film['year'] ?? '', ENT_QUOTES);
        $genre = htmlspecialchars($film['genre'] ?? '', ENT_QUOTES);
        $username = htmlspecialchars($film['username'] ?? 'unknown', ENT_QUOTES);
        $date = htmlspecialchars($film['date_uploaded'] ?? '', ENT_QUOTES);

        echo "<div class='film-card'>";
        if ($cover) {
            echo "<img src='" . $cover . "' alt='" . $title . "' />";
        }
        echo "<div class='film-info'>";
        echo "<h3>" . $title . "</h3>";
        echo "<p><b>Режисьор:</b> " . $director . "</p>";
        if (!empty($film['actors'])) {
            echo "<p><b>Актьори:</b> " . htmlspecialchars(implode(', ', $film['actors']), ENT_QUOTES) . "</p>";
        }
        echo "<p>" . $desc . "</p>";
        echo "<p class='small'><b>Публикувал:</b> " . $username . " — " . $date . "</p>";
        echo "<div class='film-actions'>";
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $film['user_id']) {
            // Edit form (POST content_id to edit page)
            echo "<form class='edit-film-form' method='POST' action='api/films/edit.php' style='display:inline;margin-right:6px;'>";
            echo "<input type='hidden' name='content_id' value='" . htmlspecialchars($film['content_id'], ENT_QUOTES) . "' />";
            echo "<button type='submit'>Редактирай</button>";
            echo "</form>";

            // Delete form (POST content_id to delete page) with confirm
            echo "<form class='delete-film-form' method='POST' action='api/films/delete.php' style='display:inline;' onsubmit=\"return confirm('Наистина ли искате да изтриете този материал?');\">";
            echo "<input type='hidden' name='content_id' value='" . htmlspecialchars($film['content_id'], ENT_QUOTES) . "' />";
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
