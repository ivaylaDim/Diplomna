<?php
session_start();

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
    <title>Статии — Български Културен Архив</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/script/app.js" defer></script>
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

    <div class="container">
        <h1>Статии</h1>

        <button id="reload-articles">Презареди</button>
        <span id="loader-articles" style="display:none;">Зареждане...</span>
        <div id="message-articles" class="error"></div>

        <div id="articles">
                <div class="table-wrap">
                <table id="articles-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Publication</th>
                            <th>Date</th>
                            <?php if (in_array($_SESSION['role'] ?? 'user', ['moderator','administrator'])): ?>
                                <th class="actions">Действия</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- rows populated via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</body>
</html>
