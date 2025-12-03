<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Български Културен Архив</title>
    <link rel="stylesheet" href="assets/style.css">

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
                    <li><a href="/texts">Текстове</a></li>
                    <li><a href="/images">Изображения</a></li>
                    <li><a href="/posters">Плакати</a></li>
                    <li><a href="/books">Книги</a></li>
                    <li><a href="/about">За нас</a></li>
                    <li><a href="/upload">Качи материал</a></li>
                </ul>
            </nav>
            <div class="user-actions">
                <a href="./login.php" class="btn-login">Вход</a>
                <a href="./register.php" class="btn-register">Регистрация</a>
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
                        <img src="/images/theater-icon.png" alt="Театър">
                        <h4>Театрални плакати</h4>
                        <p>Архив на плакати от български театри</p>
                        <a href="/collections/theater-posters">Разгледай</a>
                    </div>
                    <div class="collection-item">
                        <img src="/images/opera-icon.png" alt="Опера">
                        <h4>Оперни сценарии</h4>
                        <p>Либрета и сценарии на български опери</p>
                        <a href="/collections/opera-scripts">Разгледай</a>
                    </div>
                    <div class="collection-item">
                        <img src="/images/books-icon.png" alt="Книги">
                        <h4>Български издания</h4>
                        <p>Корици и съдържание на книги в публичен домейн</p>
                        <a href="/collections/books">Разгледай</a>
                    </div>
                    <div class="collection-item">
                        <img src="/images/manuscripts-icon.png" alt="Ръкописи">
                        <h4>Исторически документи</h4>
                        <p>Дигитализирани исторически материали</p>
                        <a href="/collections/documents">Разгледай</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="featured">
            <div class="container">
                <h3>Препоръчани материали</h3>
                <div class="featured-grid">
                    <div class="featured-item">
                        <img src="/uploads/thumbnails/sample1.jpg" alt="Избран материал">
                        <h5>Плакат "Хамлет" - Народен театър 1985</h5>
                        <p>Автор: неизвестен</p>
                    </div>
                    <div class="featured-item">
                        <img src="/uploads/thumbnails/sample2.jpg" alt="Избран материал">
                        <h5>Корица на "Под игото" - първо издание</h5>
                        <p>Иван Вазов, 1894 г.</p>
                    </div>
                    <div class="featured-item">
                        <img src="/uploads/thumbnails/sample3.jpg" alt="Избран материал">
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
                    <a href="/upload" class="btn-primary">Качи материал</a>
                    <a href="/volunteer" class="btn-secondary">Стани доброволец</a>
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
                        <li><a href="/contact">Контакти</a></li>
                        <li><a href="/privacy">Политика за поверителност</a></li>
                        <li><a href="/terms">Условия за ползване</a></li>
                        <li><a href="/donate">Направи дарение</a></li>
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

    <script src="script.js"></script>
</body>
</html>