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
    <title>Подаване на материал — Български Културен Архив</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/script/app.js" defer></script>
    <script>
        
    </script>
</head>
<body>
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
<body>
<main class="container">
    <h1>Филми</h1>

    <button id="reload">Презареди</button>
    <span id="loader">Зареждане...</span>
    <div id="message" class="error"></div>

    <div id="films">
        <!-- load films -->
    </div>

</main>




</body>
</html>