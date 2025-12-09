<?php
require_once __DIR__ . '/../../db.php';
session_start();

// Defensive: convert runtime PHP errors/exceptions to JSON responses
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
set_error_handler(function($errno, $errstr, $errfile, $errline){
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status'=>'error','message'=>"PHP error: $errstr in $errfile:$errline"]);
    exit;
});
set_exception_handler(function($e){
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    exit;
});

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
                    t.showrunner AS showrunner,
                    t.seasons AS seasons,
                    t.episodes AS episodes,
                    u.username AS username
                FROM contents c
                INNER JOIN tv_shows t ON t.content_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.type = 'tv' AND (c.title LIKE ? OR t.showrunner LIKE ? OR u.username LIKE ?)
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
                    t.showrunner AS showrunner,
                    t.seasons AS seasons,
                    t.episodes AS episodes,
                    u.username AS username
                FROM contents c
                INNER JOIN tv_shows t ON t.content_id = c.id
                INNER JOIN users u ON c.user_id = u.id
                WHERE c.type = 'tv'
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

    // Render HTML cards for TV shows
    foreach ($rows as $tv) {
        $cover = htmlspecialchars($tv['cover_path'] ?? '', ENT_QUOTES);
        $title = htmlspecialchars($tv['title'] ?? '', ENT_QUOTES);
        $desc = htmlspecialchars($tv['description'] ?? '', ENT_QUOTES);
        $showrunner = htmlspecialchars($tv['showrunner'] ?? '', ENT_QUOTES);
        $seasons = htmlspecialchars($tv['seasons'] ?? '', ENT_QUOTES);
        $episodes = htmlspecialchars($tv['episodes'] ?? '', ENT_QUOTES);
        $year = htmlspecialchars($tv['year'] ?? '', ENT_QUOTES);
        $genre = htmlspecialchars($tv['genre'] ?? '', ENT_QUOTES);
        $username = htmlspecialchars($tv['username'] ?? 'unknown', ENT_QUOTES);
        $date = htmlspecialchars($tv['date_uploaded'] ?? '', ENT_QUOTES);

        echo "<div class='tv-card'>";
        if ($cover) {
            echo "<img src='" . $cover . "' alt='" . $title . "' />";
        }
        echo "<div class='tv-info'>";
        echo "<h3>" . $title . "</h3>";
        if ($showrunner) echo "<p><b>Showrunner:</b> " . $showrunner . "</p>";
        if ($seasons) echo "<p><b>Seasons:</b> " . $seasons . "</p>";
        if ($episodes) echo "<p><b>Episodes:</b> " . $episodes . "</p>";
        if ($year) echo "<p><b>Year:</b> " . $year . "</p>";
        if ($genre) echo "<p><b>Genre:</b> " . $genre . "</p>";
        echo "<p>" . $desc . "</p>";
        echo "<p class='small'><b>Публикувал:</b> " . $username . " — " . $date . "</p>";
        echo "<div class='tv-actions'>";
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $tv['user_id']) {
            echo "<form class='edit-tv-form' method='POST' action='api/tv/edit.php' style='display:inline;margin-right:6px;'>";
            echo "<input type='hidden' name='content_id' value='" . htmlspecialchars($tv['content_id'], ENT_QUOTES) . "' />";
            echo "<button type='submit' class='primary'>Редактирай</button>";
            echo "</form>";

            echo "<form class='delete-tv-form' method='POST' action='api/tv/delete.php' style='display:inline;' onsubmit=\"return confirm('Наистина ли искате да изтриете този материал?');\">";
            echo "<input type='hidden' name='content_id' value='" . htmlspecialchars($tv['content_id'], ENT_QUOTES) . "' />";
            echo "<button type='submit' class='danger'>Изтрий</button>";
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

