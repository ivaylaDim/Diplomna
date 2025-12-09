<?php
require_once __DIR__ . '/../../db.php';
session_start();

// Optional params: limit, offset, q (search)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$q = trim($_GET['q'] ?? '');

// Cap limit to avoid heavy queries
if ($limit < 1) $limit = 1;
if ($limit > 200) $limit = 200;
if ($offset < 0) $offset = 0;

header('Content-Type: application/json; charset=utf-8');

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
        // ensure published_date string exists
        if (empty($row['published_date'])) $row['published_date'] = '';
        $rows[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $rows]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
