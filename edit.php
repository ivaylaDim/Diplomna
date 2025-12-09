<!-- edit and delete form. delete option only when user role=admin or moderator -->

<?php
session_start();

require_once __DIR__ . '/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: log_in.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Подаване на материал — Български Културен Архив</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/script/app.js" defer></script>
    <script>
        
    </script>
</head>
<body data-role="<?= htmlspecialchars($_SESSION['role'] ?? 'guest', ENT_QUOTES) ?>">
<header class="site-header">
    <div class="container">
        <div class="logo">
            <h1><a href="/">Български Културен Архив</a></h1>
            <p>Дигитални архиви</p>
        </div>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Начало</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div id="edit-root" data-content-id="<?= isset($_GET['content_id']) ? (int)$_GET['content_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : '') ?>">
        <div class="card">
            <div id="edit-form-container">Зареждане...</div>
        </div>
    </div>
</main>
</body>
</html>