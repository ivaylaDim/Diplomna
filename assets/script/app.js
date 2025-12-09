document.addEventListener('DOMContentLoaded', function () {
	// Find the login form that posts to api/login.php
	const loginForm = document.querySelector('form[action="api/login.php"]');
	if (!loginForm) return;

	// Insert a place for inline messages

	loginForm.addEventListener('submit', async function (e) {
		e.preventDefault();

		const formData = new FormData(loginForm);

		try {
			const resp = await fetch(loginForm.action, {
				method: 'POST',
				body: formData,
				headers: {
					'Accept': 'application/json'
				}
			});

			// Try to parse JSON; if not JSON, handle HTML/text responses (server-side redirects or form handlers)
			let data = null;
			try {
				data = await resp.json();
			} catch (jsonErr) {
				// Not JSON — inspect content-type and body
				const ct = (resp.headers.get('content-type') || '').toLowerCase();
				const text = await resp.text().catch(() => '');
				// If server returned HTML (likely followed a redirect), navigate the browser to the final URL
				if (ct.includes('text/html') || resp.redirected) {
					// If fetch followed a redirect, resp.url is the redirected page (e.g., index.php)
					window.location.href = resp.url || 'index.php';
					return;
				}
				// Otherwise show server text as error message
					Swal.fire({
						icon: 'error',
						title: 'Сървърна грешка',
						text: text || `Invalid JSON response (status ${resp.status} ${resp.statusText})`
					});
				return;
			}

			if (data.status === 'success') {
					const msgText = data.message || 'Входът беше успешен';
					Swal.fire({
						icon: 'success',
						title: msgText,
						timer: 900,
						showConfirmButton: false
					}).then(() => {
						window.location.href = data.redirect || 'index.php';
					});
			} else {
					Swal.fire({
						icon: 'error',
						title: 'Грешка при вход',
						text: data.message || 'Грешка при вход.'
					});
			}
		} catch (err) {
			console.error(err);
				Swal.fire({
					icon: 'error',
					title: 'Грешка',
					text: 'Грешка при изпращане. Моля опитайте отново.'
				});
		}
	});
});

// delegated handler for delete buttons (works for dynamically added rows)
$(document).on('click', '.delete-btn', function(e){
	e.preventDefault();
	var id = $(this).data('id');
	if (!id) return;
	Swal.fire({
		title: 'Сигурни ли сте?',
		text: 'Това действие не може да бъде върнато.',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Да, изтрий',
		cancelButtonText: 'Отказ'
	}).then(function(result){
		if (!result.isConfirmed) return;
		var fd = new FormData(); fd.append('content_id', id);
		fetch('api/delete.php', { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
			.then(function(resp){ return resp.json().catch(function(){ return null; }); })
			.then(function(data){
				if (data && data.status === 'success') {
					Swal.fire({ icon: 'success', title: data.message || 'Изтрито', timer: 1000, showConfirmButton: false });
					// reload lists where relevant
					if (typeof loadFilms === 'function') try { loadFilms(); } catch(e){}
					if (typeof loadBooks === 'function') try { loadBooks(); } catch(e){}
					if (typeof loadArticles === 'function') try { loadArticles(); } catch(e){}
					if (typeof loadTV === 'function') try { loadTV(); } catch(e){}
				} else {
					Swal.fire({ icon: 'error', title: 'Грешка', text: (data && data.message) ? data.message : 'Неуспешно изтриване.' });
				}
			}).catch(function(err){
				console.error(err);
				Swal.fire({ icon: 'error', title: 'Грешка', text: 'Неуспешно изтриване.' });
			});
	});
});


//registration form handler
$(document).ready(function () {
    const regForm = $('form[action="api/register.php"]');
    if (regForm.length === 0) return;


    regForm.on('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(regForm[0]);

        try {
            const resp = await fetch(regForm[0].action, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });

            let data = null;
            try {
                data = await resp.json();
            } catch (jsonErr) {
                const ct = (resp.headers.get('content-type') || '').toLowerCase();
                const text = await resp.text().catch(() => '');
                if (ct.includes('text/html') || resp.redirected) {
                    window.location.href = resp.url || 'index.php';
                    return;
                }
				Swal.fire({
					icon: 'error',
					title: 'Сървърна грешка',
					text: text || `Invalid JSON response (status ${resp.status} ${resp.statusText})`
				});
                return;
            }

			if (data.status === 'success') {
				const msgText = data.message || 'Регистрацията е успешна';
				Swal.fire({
					icon: 'success',
					title: msgText,
					timer: 1200,
					showConfirmButton: false
				});
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Грешка при регистрация',
					text: data.message || 'Грешка при регистрация.'
				});
            }
        } catch (err) {
            console.error(err);
			Swal.fire({
				icon: 'error',
				title: 'Грешка',
				text: 'Грешка при изпращане. Моля опитайте отново.'
			});
        }
    });
});


//submission form handler
$(document).ready(function () {
	const submissionForm = $('#submission-form');
	if (submissionForm.length === 0) return;

	submissionForm.on('submit', async function (e) {
		e.preventDefault();

		const contentType = $('#content-type').val();
		if (!contentType) {
			Swal.fire({
				icon: 'warning',
				title: 'Избери тип',
				text: 'Моля, избери тип на съдържанието.'
			});
			return;
		}

		const formData = new FormData(submissionForm[0]);

		try {
			const resp = await fetch(submissionForm[0].action, {
				method: 'POST',
				body: formData,
				headers: { 'Accept': 'application/json' }
			});

			let data = null;
			try {
				data = await resp.json();
			} catch (jsonErr) {
				const ct = (resp.headers.get('content-type') || '').toLowerCase();
				const text = await resp.text().catch(() => '');
				if (ct.includes('text/html') || resp.redirected) {
					window.location.href = resp.url || 'index.php';
					return;
				}
				Swal.fire({
					icon: 'error',
					title: 'Сървърна грешка',
					text: text || `Invalid JSON response (status ${resp.status} ${resp.statusText})`
				});
				return;
			}

			if (data.status === 'success') {
				const msgText = data.message || 'Материалът е успешно подаден';
				Swal.fire({
					icon: 'success',
					title: msgText,
					timer: 1200,
					showConfirmButton: false
				});
			} else {
				Swal.fire({
					icon: 'error',
					title: 'Грешка при подаване',
					text: data.message || 'Грешка при подаване на материал.'
				});
			}
		} catch (err) {
			console.error(err);
			Swal.fire({
				icon: 'error',
				title: 'Грешка',
				text: 'Грешка при изпращане. Моля опитайте отново.'
			});
		}
	});
});




// Logout button handler
$('#logoutBtn').on('click', function (e) {
	e.preventDefault();

	$.post("api/logout.php")
		.done(function (response) {
			let data = null;
			try {
				data = (typeof response === 'string') ? JSON.parse(response) : response;
			} catch (parseErr) {
				data = null;
			}

			if (data && data.status === 'success') {
				const msgText = data.message || 'Успешно излизане';
				Swal.fire({
					icon: 'success',
					title: msgText,
					timer: 1200,
					showConfirmButton: false
				}).then(() => {
					window.location.href = 'log_in.php?msg=' + encodeURIComponent(msgText);
				});
			} else {
				const errMsg = (data && data.message) ? data.message : 'Грешка при излизане.';
				Swal.fire({
					icon: 'error',
					title: 'Грешка',
					text: errMsg
				});
			}
		})
		.fail(function () {
			Swal.fire({
				icon: 'error',
				title: 'Грешка',
				text: 'Не може да се свърже със сървъра.'
			});
		});
});

//handle dynamic fields based on content type selection
$(document).ready(function () {
            $('#content-type').on('change', function () {
                const selectedType = $(this).val();
                $('.type-fields').hide();
                if (selectedType) {
                    $('#fields-' + selectedType).show();
                }
            });
 });


//films loading and rendering
$(function(){
	     function renderFilms(items){
		    if(!items || items.length === 0){
			    if ($('#films-table').length) {
				  $('#films-table tbody').html('<tr><td colspan="4">No films found.</td></tr>');
			    } else {
				  $("#films").html("<p>No films found.</p>");
			    }
			    return;
		    }

			// Determine if current user can edit/delete
			var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
			var isAdmin = (role === 'administrator' || role === 'moderator');

			// If a table skeleton exists, populate tbody only
			if ($('#films-table').length) {
				// Ensure actions header present for admins
				if (isAdmin && $('#films-table thead th.actions').length === 0) {
					$('#films-table thead tr').append('<th class="actions">Действия</th>');
				}
			    var rows = '';
			    $.each(items, function(i, f){
					rows += '<tr>'
						+ '<td>' + $('<div>').text(f.id).html() + '</td>'
						+ '<td>' + $('<div>').text(f.title).html() + '</td>'
						+ '<td>' + $('<div>').text(f.year).html() + '</td>'
						+ '<td>' + $('<div>').text(f.director).html() + '</td>';
					if (isAdmin) {
						rows += '<td><a href="edit.php?content_id=' + encodeURIComponent(f.id) + '">Редактирай</a> '
							  + '<button class="delete-btn" data-id="' + $('<div>').text(f.id).html() + '">Изтрий</button></td>';
					}
					rows += '</tr>';
			    });
			    $('#films-table tbody').html(rows);
			    return;
		    }

			    var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Year</th><th>Director</th>' + (isAdmin?'<th class="actions">Действия</th>':'') + '</tr></thead><tbody>';
		    $.each(items, function(i, f){
			    html += '<tr>'
				    + '<td>' + $('<div>').text(f.id).html() + '</td>'
				    + '<td>' + $('<div>').text(f.title).html() + '</td>'
				    + '<td>' + $('<div>').text(f.year).html() + '</td>'
				    + '<td>' + $('<div>').text(f.director).html() + '</td>'
				    + (isAdmin ? '<td><a href="edit.php?content_id=' + encodeURIComponent(f.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(f.id).html() + '">Изтрий</button></td></tr>' : '</tr>');
		    });
		    html += '</tbody></table>';
		    $("#films").html('<div class="table-wrap">' + html + '</div>');
	     }

     function loadFilms(){
          $("#loader").show();
          $("#message").text("");
		  $.ajax({
				url: 'api/films/load_films.php',
				method: 'GET',
				dataType: 'json',
				cache: false
		  }).done(function(response){
				if (!response) {
					$("#message").text('Invalid response from server');
					$("#films").empty();
					return;
				}
				if (response.status && response.status !== 'success') {
					$("#message").text(response.message || 'Error loading films');
					$("#films").empty();
					return;
				}
				var items = response.data || response;
				// Normalize items to expected fields (id, title, year, director)
				items = items.map(function(r){
					return {
						id: r.content_id || r.id || r.contentId || null,
						title: r.title || '',
						year: r.year || '',
						director: r.director || ''
					};
				});
				renderFilms(items);
		  }).fail(function(jqXHR, textStatus, errorThrown){
                var msg = "Could not load films: " + (errorThrown || textStatus);
                $("#message").text(msg);
                $("#films").empty();
          }).always(function(){
                $("#loader").hide();
          });
     }

     $("#reload").on('click', loadFilms);

     // initial load
     loadFilms();
});

//articles loading and rendering
$(function(){
    function renderArticles(items){
		if(!items || items.length === 0){
			if ($('#articles-table').length) {
				$('#articles-table tbody').html('<tr><td colspan="5">No articles found.</td></tr>');
			} else {
				$("#articles").html("<p>No articles found.</p>");
			}
			return;
		}

		if ($('#articles-table').length) {
			var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
			var isAdmin = (role === 'administrator' || role === 'moderator');
			// ensure actions header present if admin
			if (isAdmin && $('#articles-table thead th.actions').length === 0) {
				$('#articles-table thead tr').append('<th class="actions">Действия</th>');
			}
			var rows = '';
			$.each(items, function(i, a){
				rows += '<tr>'
					  + '<td>' + $('<div>').text(a.id).html() + '</td>'
					  + '<td>' + $('<div>').text(a.title).html() + '</td>'
					  + '<td>' + $('<div>').text(a.author || '').html() + '</td>'
					  + '<td>' + $('<div>').text(a.publication || '').html() + '</td>'
					  + '<td>' + $('<div>').text(a.published_date || '').html() + '</td>';
				if (isAdmin) rows += '<td><a href="edit.php?content_id=' + encodeURIComponent(a.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(a.id).html() + '">Изтрий</button></td>';
				rows += '</tr>';
			});
			$('#articles-table tbody').html(rows);
			return;
		}

		var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
		var isAdmin = (role === 'administrator' || role === 'moderator');
		var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Publication</th><th>Date</th>' + (isAdmin?'<th class="actions">Действия</th>':'') + '</tr></thead><tbody>';
		$.each(items, function(i, a){
			html += '<tr>'
			  + '<td>' + $('<div>').text(a.id).html() + '</td>'
			  + '<td>' + $('<div>').text(a.title).html() + '</td>'
			  + '<td>' + $('<div>').text(a.author || '').html() + '</td>'
			  + '<td>' + $('<div>').text(a.publication || '').html() + '</td>'
			  + '<td>' + $('<div>').text(a.published_date || '').html() + '</td>'
			  + (isAdmin ? '<td><a href="edit.php?content_id=' + encodeURIComponent(a.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(a.id).html() + '">Изтрий</button></td>' : '')
			  + '</tr>';
		});
		html += '</tbody></table>';
		$("#articles").html('<div class="table-wrap">' + html + '</div>');
    }

    function loadArticles(){
        $("#loader-articles").show();
        $("#message-articles").text("");
        $.ajax({
            url: 'api/articles/load_articles.php',
            method: 'GET',
            dataType: 'json',
            cache: false
        }).done(function(response){
            if (!response) {
                $("#message-articles").text('Invalid response from server');
                $("#articles").empty();
                return;
            }
            if (response.status && response.status !== 'success') {
                $("#message-articles").text(response.message || 'Error loading articles');
                $("#articles").empty();
                return;
            }
            var items = response.data || response;
            items = items.map(function(r){
                return {
                    id: r.content_id || r.id || null,
                    title: r.title || '',
                    author: r.author || r.article_author || '',
                    publication: r.publication || r.article_publication || '',
                    published_date: r.published_date || r.article_published_date || ''
                };
            });
            renderArticles(items);
        }).fail(function(jqXHR, textStatus, errorThrown){
            var msg = "Could not load articles: " + (errorThrown || textStatus);
            $("#message-articles").text(msg);
            $("#articles").empty();
        }).always(function(){
            $("#loader-articles").hide();
        });
    }

    $(document).ready(function(){
        $("#reload-articles").on('click', loadArticles);
        loadArticles();
    });
});

// books loading and rendering (generic, will use #books or #books-list if present)
$(function(){
	function renderBooks(items, targetSelector){
		if(!items || items.length === 0){
			if ($(targetSelector + ' table').length) {
				$(targetSelector + ' table tbody').html('<tr><td colspan="4">No books found.</td></tr>');
			} else {
				$(targetSelector).html("<p>No books found.</p>");
			}
			return;
		}

		// If table skeleton exists inside the target, populate tbody only
		if ($(targetSelector + ' table').length) {
			var rows = '';
			var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
			var isAdmin = (role === 'administrator' || role === 'moderator');
			$.each(items, function(i, b){
				rows += '<tr>'
					  + '<td>' + $('<div>').text(b.id).html() + '</td>'
					  + '<td>' + $('<div>').text(b.title).html() + '</td>'
					  + '<td>' + $('<div>').text(b.author || '').html() + '</td>'
					  + '<td>' + $('<div>').text(b.year || '').html() + '</td>';
				if (isAdmin) rows += '<td><a href="edit.php?content_id=' + encodeURIComponent(b.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(b.id).html() + '">Изтрий</button></td>';
				rows += '</tr>';
			});
			$(targetSelector + ' table tbody').html(rows);
			return;
		}

		var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
		var isAdmin = (role === 'administrator' || role === 'moderator');
		var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Year</th>' + (isAdmin?'<th class="actions">Действия</th>':'') + '</tr></thead><tbody>';
		$.each(items, function(i, b){
		html += '<tr>'
			  + '<td>' + $('<div>').text(b.id).html() + '</td>'
			  + '<td>' + $('<div>').text(b.title).html() + '</td>'
			  + '<td>' + $('<div>').text(b.author || '').html() + '</td>'
			  + '<td>' + $('<div>').text(b.year || '').html() + '</td>'
			  + (isAdmin ? '<td><a href="edit.php?content_id=' + encodeURIComponent(b.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(b.id).html() + '">Изтрий</button></td>' : '')
			  + '</tr>';
		});
		html += '</tbody></table>';
		$(targetSelector).html('<div class="table-wrap">' + html + '</div>');
	}

	function loadBooks(){
		var target = null;
		if ($('#books').length) target = '#books';
		else if ($('#books-list').length) target = '#books-list';
		else return; // no books container on page

		var loader = $('#loader-books');
		var message = $('#message-books');
		if (loader.length === 0) loader = $("<span style='display:none;' id='loader-books'>Loading...</span>").insertBefore(target);
		if (message.length === 0) message = $("<div id='message-books' class='error'></div>").insertBefore(target);

		loader.show();
		message.text("");
		$.ajax({
			url: 'api/books/load_books.php',
			method: 'GET',
			dataType: 'json',
			cache: false
		}).done(function(response){
			if (!response) {
				message.text('Invalid response from server');
				$(target).empty();
				return;
			}
			if (response.status && response.status !== 'success') {
				message.text(response.message || 'Error loading books');
				$(target).empty();
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
			renderBooks(items, target);
		}).fail(function(jqXHR, textStatus, errorThrown){
			var msg = "Could not load books: " + (errorThrown || textStatus);
			message.text(msg);
			$(target).empty();
		}).always(function(){
			loader.hide();
		});
	}

	$(document).ready(function(){
		$('#reload-books').on('click', loadBooks);
		loadBooks();
	});
});

// tv loading and rendering
$(function(){
	function renderTV(items){
		if(!items || items.length === 0){
			if ($('#tv-table').length) {
				$('#tv-table tbody').html('<tr><td colspan="5">No TV shows found.</td></tr>');
			} else {
				$("#tv").html("<p>No TV shows found.</p>");
			}
			return;
		}

		var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
		var isAdmin = (role === 'administrator' || role === 'moderator');

		if ($('#tv-table').length) {
			// ensure actions header present if admin
			if (isAdmin && $('#tv-table thead th.actions').length === 0) {
				$('#tv-table thead tr').append('<th class="actions">Действия</th>');
			}
			var rows = '';
			$.each(items, function(i, t){
				rows += '<tr>'
				  + '<td>' + $('<div>').text(t.id).html() + '</td>'
				  + '<td>' + $('<div>').text(t.title).html() + '</td>'
				  + '<td>' + $('<div>').text(t.seasons || '').html() + '</td>'
				  + '<td>' + $('<div>').text(t.episodes || '').html() + '</td>'
				  + '<td>' + $('<div>').text(t.showrunner || '').html() + '</td>';
				if (isAdmin) rows += '<td><a href="edit.php?content_id=' + encodeURIComponent(t.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(t.id).html() + '">Изтрий</button></td>';
				rows += '</tr>';
			});
			$('#tv-table tbody').html(rows);
			return;
		}

		var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Seasons</th><th>Episodes</th><th>Showrunner</th>' + (isAdmin?'<th class="actions">Действия</th>':'') + '</tr></thead><tbody>';
		$.each(items, function(i, t){
			html += '<tr>'
			  + '<td>' + $('<div>').text(t.id).html() + '</td>'
			  + '<td>' + $('<div>').text(t.title).html() + '</td>'
			  + '<td>' + $('<div>').text(t.seasons || '').html() + '</td>'
			  + '<td>' + $('<div>').text(t.episodes || '').html() + '</td>'
			  + '<td>' + $('<div>').text(t.showrunner || '').html() + '</td>'
			  + (isAdmin ? '<td><a href="edit.php?content_id=' + encodeURIComponent(t.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(t.id).html() + '">Изтрий</button></td>' : '')
			  + '</tr>';
		});
		html += '</tbody></table>';
		$("#tv").html('<div class="table-wrap">' + html + '</div>');
	}

	function loadTV(){
		if ($('#tv').length === 0) return;
		var loader = $('#loader-tv');
		var message = $('#message-tv');
		if (loader.length === 0) loader = $("<span style='display:none;' id='loader-tv'>Loading...</span>").insertBefore('#tv');
		if (message.length === 0) message = $("<div id='message-tv' class='error'></div>").insertBefore('#tv');

		loader.show();
		message.text("");
		$.ajax({
			url: 'api/tv/load_tv.php',
			method: 'GET',
			dataType: 'json',
			cache: false
		}).done(function(response){
			if (!response) {
				message.text('Invalid response from server');
				$('#tv').empty();
				return;
			}
			if (response.status && response.status !== 'success') {
				message.text(response.message || 'Error loading TV shows');
				$('#tv').empty();
				return;
			}
			var items = response.data || response;
			items = items.map(function(r){
				return {
					id: r.content_id || r.id || null,
					title: r.title || '',
					showrunner: r.showrunner || '',
					seasons: r.seasons || '',
					episodes: r.episodes || ''
				};
			});
			renderTV(items);
		}).fail(function(jqXHR, textStatus, errorThrown){
			var msg = "Could not load TV shows: " + (errorThrown || textStatus);
			message.text(msg);
			$('#tv').empty();
		}).always(function(){
			loader.hide();
		});
	}

	$(document).ready(function(){
		$('#reload-tv').on('click', loadTV);
		loadTV();
	});
});

