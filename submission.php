<!-- submission form. fields according to db - title, content, author, date_created, image (optional) -->
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
    <title>Подаване на материал — Български Културен Архив</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/script/app.js" defer></script>

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
        <div class="user-actions">
            <a href="log_in.php" class="btn-login">Вход</a>
            <a href="register.php" class="btn-register">Регистрация</a>
        </div>
    </div>
</header>
<main>
    <section class="hero">
        <div class="container">
            <div class="card submission-card">
                <div class="submission-forms">
                    <!-- Submission panel -->
                    <div class="panel submission">
                        <div>
                            <h2>Подаване на материал</h2>
                            <form action="api/submit.php" method="POST" enctype="multipart/form-data" id="submission-form">
                                <!-- Content Type Selection -->
                                <label for="content-type">Тип на съдържанието</label>
                                <select id="content-type" name="type" required>
                                    <option value="">-- Избери тип --</option>
                                    <option value="book">Книга</option>
                                    <option value="film">Филм</option>
                                    <option value="tv">ТВ Сериал</option>
                                    <option value="article">Статия</option>
                                </select>

                                <!-- Common Fields -->
                                <label for="sub-title">Заглавие</label>
                                <input id="sub-title" name="title" type="text" required>

                                <label for="sub-description">Описание</label>
                                <textarea id="sub-description" name="description" rows="5" required></textarea>

                                <label for="sub-year">Година</label>
                                <input id="sub-year" name="year" type="number" min="1800" max="2099">
                                <!-- #TODO create dropdown list with genres and field for link to download-->
                                <label for="sub-genre">Жанр</label>
                                <input id="sub-genre" name="genre" type="text">

                                <label for="sub-image">Изображение (корица)</label>
                                <input id="sub-image" name="cover_path" type="file" accept="image/*">

                                <!-- Book Fields -->
                                <fieldset id="fields-book" class="type-fields" style="display: none;">
                                    <legend>Данни за книга</legend>
                                    <label for="book-author">Автор</label>
                                    <input id="book-author" name="book_author" type="text">
                                </fieldset>

                                <!-- Film Fields -->
                                <fieldset id="fields-film" class="type-fields" style="display: none;">
                                    <legend>Данни за филм</legend>
                                    <label for="film-director">Режисьор</label>
                                    <input id="film-director" name="film_director" type="text">
                                    <label for="film-actors">Актьори</label>
                                    <textarea id="film-actors" name="film_actors" rows="3"></textarea>
                                </fieldset>

                                <!-- TV Fields -->
                                <fieldset id="fields-tv" class="type-fields" style="display: none;">
                                    <legend>Данни за ТВ сериал</legend>
                                    <label for="tv-showrunner">Создател на сериала</label>
                                    <input id="tv-showrunner" name="tv_showrunner" type="text">
                                    <label for="tv-seasons">Брой сезони</label>
                                    <input id="tv-seasons" name="tv_seasons" type="number" min="1">
                                    <label for="tv-episodes">Брой епизоди</label>
                                    <input id="tv-episodes" name="tv_episodes" type="number" min="1">
                                </fieldset>

                                <!-- Article Fields -->
                                <fieldset id="fields-article" class="type-fields" style="display: none;">
                                    <legend>Данни за статия</legend>
                                    <label for="article-author">Автор</label>
                                    <input id="article-author" name="article_author" type="text">
                                    <label for="article-publication">Издание</label>
                                    <input id="article-publication" name="article_publication" type="text">
                                    <label for="article-published-date">Дата на публикуване</label>
                                    <input id="article-published-date" name="article_published_date" type="date">
                                </fieldset>

                                <button type="submit">Подай материал</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
</body>