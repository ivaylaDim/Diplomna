<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: log_in.php");
    exit;
}
#TODO normalise head tags in every file
?>


<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Книги — Български Културен Архив</title>
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
        <h1>Книги</h1>

        <button id="reload-books">Презареди</button>
        <span id="loader-books" style="display:none;">Loading...</span>
        <div id="message-books" class="error"></div>

        <div id="books-list">
                <div class="table-wrap">
                <table id="books-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Year</th>
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
