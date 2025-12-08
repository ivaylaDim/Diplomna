
<?php

#main page. show collections, featured items, search bar, redirect to login/register if not logged in

session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: log_in.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Български Културен Архив</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/script/app.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <h1><a href="/">Български Културен Архив</a></h1>
                <p>Дигитални архиви на българската култура</p>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="./articles.php">Статии</a></li>
                    <li><a href="./books.php">Книги</a></li>
                    <li><a href="./films.php">Филми</a></li>
                    <li><a href="./tv.php">Сериали</a></li>
                    <li><a href="#">За нас</a></li>
                    <li><a href="./submission.php">Качи материал</a></li>
                </ul>
            </nav>
            <?php
            $username = 'Потребител';

            if (!empty($_SESSION['username'])) {
                $username = $_SESSION['username'];
            }

            $username = htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            ?>
            <span class="user-name">Привет, <?php echo $username; ?></span>
            <div>
                <button type="submit" id="logoutBtn" class="btn-logout">Изход</button>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Съхраняване на българското културно наследство</h2>
                <p>Уеб платформа за архивиране на плакати, сценарии, книги и други културни материали</p>
                <div class="search-box">
                    <form action="/search" method="GET">
                        <!-- #TODO implement search -->
                        <input type="text" name="q" placeholder="Търсете в архива..." class="search-input">
                        <button type="submit" class="search-btn">Търси</button>
                    </form>
                </div>
            </div>
        </section>

        <section class="collections">
            <div class="container">
                <h3>Колекции</h3>
                <div class="collection-grid">
                    <div class="collection-item">
                        <img src="/img/carmen_tile.jpg" alt="Театър">
                        <h4>Театрални плакати</h4>
                        <p>Архив на плакати от български театри</p>
                        <a href="#">Разгледай</a>
                    </div>
                    <div class="collection-item">
                        <img src="/img/faust_tile.jpg" alt="Опера"> <!-- #TODO change cards to db content-->
                        <h4>Оперни Корици</h4>
                        <p>Либрета и корици на български опери</p>
                        <a href="#">Разгледай</a>
                    </div>
                    <div class="collection-item">
                        <img src="/img/tutun_tile.jpg" alt="Книги">
                        <h4>Български издания</h4>
                        <p>Корици и съдържание на книги в публичен домейн</p>
                        <a href="#">Разгледай</a>
                    </div>
                    <div class="collection-item">
                        <img src="/img/script_tile.jpg" alt="Сценарии">
                        <h4>Сценарии от постановки</h4>
                        <p>Дигитализирани исторически материали</p>
                        <a href="#">Разгледай</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="featured">
            <div class="container">
                <h3>Препоръчани материали</h3>
                <div class="featured-grid">
                    <div class="featured-item">
                        <img src="#" alt="Избран материал">
                        <h5>Плакат "Хамлет" - Народен театър 1985</h5>
                        <p>Автор: неизвестен</p>
                    </div>
                    <div class="featured-item">
                        <img src="#" alt="Избран материал">
                        <h5>Корица на "Под игото" - първо издание</h5>
                        <p>Иван Вазов, 1894 г.</p>
                    </div>
                    <div class="featured-item">
                        <img src="#" alt="Избран материал">
                        <h5>Програма от "Кармен" - София опера</h5>
                        <p>Премиера 1978 г.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="contribute">
            <div class="container">
                <h3>Участвайте в архива</h3>
                <p>Всеки с акаунт може да изпраща материали за модерация и публикуване</p>
                <div class="contribute-actions">
                    <a href="./submission.php" class="btn-primary">Качи материал</a>
                    <a href="#" class="btn-secondary">Стани доброволец</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>За проекта</h4>
                    <p>Non-profit платформа за съхраняване на българското културно наследство</p>
                    <p>Финансиране: Министерство на културата и дарения</p>
                </div>
                <div class="footer-section">
                    <h4>Връзки</h4>
                    <ul>
                        <li><a href="#">Контакти</a></li>
                        <li><a href="#">Политика за поверителност</a></li>
                        <li><a href="#">Условия за ползване</a></li>
                        <li><a href="#">Направи дарение</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Партньори</h4>
                    <ul>
                        <li>Министерство на културата</li>
                        <li>Национална библиотека</li>
                        <li>Български културни институции</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Български Културен Архив. Всички права запазени.</p>
            </div>
        </div>
    </footer>

</body>
</html>