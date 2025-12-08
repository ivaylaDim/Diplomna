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

<main class="container">
    <h1>Книги</h1>

    <button id="reload-books">Презареди</button>
    <span id="loader-books" style="display:none;">Loading...</span>
    <div id="message-books" class="error"></div>

    <div id="books-list">
         <!-- Books will be loaded here -->
    </div>
</main>

<script>
(function(){
    function renderBooks(items){
        if(!items || items.length === 0){
            $("#books-list").html("<p>No books found.</p>");
            return;
        }
        var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Year</th></tr></thead><tbody>';
        $.each(items, function(i, b){
            html += '<tr>'
                  + '<td>' + $('<div>').text(b.id).html() + '</td>'
                  + '<td>' + $('<div>').text(b.title).html() + '</td>'
                  + '<td>' + $('<div>').text(b.author || '').html() + '</td>'
                  + '<td>' + $('<div>').text(b.year || '').html() + '</td>'
                  + '</tr>';
        });
        html += '</tbody></table>';
        $("#books-list").html(html);
    }

    function loadBooks(){
        $("#loader-books").show();
        $("#message-books").text("");
        $.ajax({
            url: 'api/books/load_books.php',
            method: 'GET',
            dataType: 'json',
            cache: false
        }).done(function(response){
            if (!response) {
                $("#message-books").text('Invalid response from server');
                $("#books-list").empty();
                return;
            }
            if (response.status && response.status !== 'success') {
                $("#message-books").text(response.message || 'Error loading books');
                $("#books-list").empty();
                return;
            }
            var items = response.data || response;
            items = items.map(function(r){
                return {
                    id: r.content_id || r.id || null,
                    title: r.title || '',
                    author: r.author || r.book_author || '',
                    year: r.year || ''
                };
            });
            renderBooks(items);
        }).fail(function(jqXHR, textStatus, errorThrown){
            var msg = "Could not load books: " + (errorThrown || textStatus);
            $("#message-books").text(msg);
            $("#books-list").empty();
        }).always(function(){
            $("#loader-books").hide();
        });
    }

    $(document).ready(function(){
        $("#reload-books").on('click', loadBooks);
        loadBooks();
    });
})();
</script>

</body>
</html>
