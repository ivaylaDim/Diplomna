<!-- edit and delete form. delete option only when user role=admin -->

<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: log_in.php");
    exit;
}
require_once __DIR__ . '/db.php';

// determine id from GET or POST
$id = 0;
if (isset($_GET['content_id'])) $id = (int)$_GET['content_id'];
elseif (isset($_GET['id'])) $id = (int)$_GET['id'];
elseif (isset($_POST['id'])) $id = (int)$_POST['id'];

$row = null;
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM contents WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
    }
}

// Ensure $row is at least an array with empty values to avoid undefined variable notices
if (!is_array($row)) {
    $row = ['id' => '', 'title' => '', 'content' => ''];
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
<?php if (empty($row['id'])): ?>
    <div class="card"><p>Няма избрано съдържание за редакция.</p></div>
<?php else: ?>
    <?php
        // choose API endpoint for this type
        $endpoint = 'api/submit.php';
        if ($type === 'book') $endpoint = 'api/books/edit.php';
        if ($type === 'film') $endpoint = 'api/films/edit.php';
        if ($type === 'tv') $endpoint = 'api/tv/edit.php';
        if ($type === 'article') $endpoint = 'api/articles/edit_articles.php';
    ?>
    <form action="<?= htmlspecialchars($endpoint, ENT_QUOTES) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="content_id" value="<?php echo (int)$row['id']; ?>">

        <label>Заглавие</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($row['title'] ?? '', ENT_QUOTES); ?>">

        <label>Описание</label>
        <textarea name="description"><?php echo htmlspecialchars($row['description'] ?? '', ENT_QUOTES); ?></textarea>

        <label>Година</label>
        <input type="number" name="year" value="<?php echo htmlspecialchars($row['year'] ?? '', ENT_QUOTES); ?>">

        <label>Жанр</label>
        <input type="text" name="genre" value="<?php echo htmlspecialchars($row['genre'] ?? '', ENT_QUOTES); ?>">

        <label>Корица (нов файл ако искате да смените)</label>
        <input type="file" name="cover_path" accept="image/*">

        <?php if ($type === 'book'): ?>
            <label>Автор</label>
            <input type="text" name="book_author" value="<?php echo htmlspecialchars($typeRow['author'] ?? '', ENT_QUOTES); ?>">
        <?php elseif ($type === 'film'): ?>
            <label>Режисьор</label>
            <input type="text" name="film_director" value="<?php echo htmlspecialchars($typeRow['director'] ?? '', ENT_QUOTES); ?>">
            <label>Актьори</label>
            <input type="text" name="film_actors" value="<?php echo htmlspecialchars($typeRow['actors'] ?? '', ENT_QUOTES); ?>">
        <?php elseif ($type === 'tv'): ?>
            <label>Шоурънър</label>
            <input type="text" name="tv_showrunner" value="<?php echo htmlspecialchars($typeRow['showrunner'] ?? '', ENT_QUOTES); ?>">
            <label>Сезони</label>
            <input type="number" name="tv_seasons" value="<?php echo htmlspecialchars($typeRow['seasons'] ?? '', ENT_QUOTES); ?>">
            <label>Епизоди</label>
            <input type="number" name="tv_episodes" value="<?php echo htmlspecialchars($typeRow['episodes'] ?? '', ENT_QUOTES); ?>">
        <?php elseif ($type === 'article'): ?>
            <label>Автор</label>
            <input type="text" name="article_author" value="<?php echo htmlspecialchars($typeRow['author'] ?? '', ENT_QUOTES); ?>">
            <label>Публикация</label>
            <input type="text" name="article_publication" value="<?php echo htmlspecialchars($typeRow['publication'] ?? '', ENT_QUOTES); ?>">
            <label>Дата</label>
            <input type="date" name="article_published_date" value="<?php echo htmlspecialchars($typeRow['published_date'] ?? '', ENT_QUOTES); ?>">
        <?php endif; ?>

        <div style="margin-top:0.75rem">
            <button type="submit" name="update" class="btn-primary">Запази</button>
            <?php if (in_array($_SESSION['role'] ?? 'user', ['moderator','administrator'])): ?>
                <button type="button" class="danger delete-btn" data-id="<?php echo (int)$row['id']; ?>">Изтрий</button>
            <?php endif; ?>
        </div>
    </form>
<?php endif; ?>
</main>
</body>
</html>