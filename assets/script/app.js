document.addEventListener('DOMContentLoaded', function () {
	const loginForm = document.querySelector('form[action="api/login.php"]');
	if (!loginForm) return;


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
// Helper utilities
function getUserRole(){
	return (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
}
function userIsAdmin(){
	var r = getUserRole();
	return (r === 'administrator' || r === 'moderator');
}
function getDownload(obj){
	return (obj && (obj.download_link || obj.download)) ? (obj.download_link || obj.download) : '';
}


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




// logout button handler
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
									$('#films-table tbody').html('<tr><td colspan="6">Няма намерени филми.</td></tr>');
								} else {
									$("#films").html("<p>Няма намерени филми.</p>");
								}
			    return;
		    }

			// Determine if current user can edit/delete
			var isAdmin = userIsAdmin();

			// If a table skeleton exists, populate tbody only
				if ($('#films-table').length) {
				var rows = '';
			    $.each(items, function(i, f){
					rows += '<tr>'
						+ '<td>' + $('<div>').text(f.id).html() + '</td>'
						+ '<td>' + $('<div>').text(f.title).html() + '</td>'
						+ '<td>' + $('<div>').text(f.year).html() + '</td>'
						+ '<td>' + $('<div>').text(f.director).html() + '</td>'
						+ '<td>' + (f.download ? '<a href="' + $('<div>').text(f.download).html() + '" target="_blank" rel="noopener">Изтегли</a>' : '') + '</td>';
					if (isAdmin) {
						rows += '<td><a href="edit.php?content_id=' + encodeURIComponent(f.id) + '">Редактирай</a> '
							  + '<button class="delete-btn" data-id="' + $('<div>').text(f.id).html() + '">Изтрий</button></td>';
					}
					rows += '</tr>';
			    });
			    $('#films-table tbody').html(rows);
			    return;
		    }

				var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Year</th><th>Director</th><th>Линк</th>' + (isAdmin?'<th class="actions">Действия</th>':'') + '</tr></thead><tbody>';
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
					$("#message").text('Невалиден отговор от сървъра');
					$("#films").empty();
					return;
				}
				if (response.status && response.status !== 'success') {
					$("#message").text(response.message || 'Грешка при зареждане на филмите');
					$("#films").empty();
					return;
				}
				var items = response.data || response;
				items = items.map(function(r){
					return {
						id: r.content_id || r.id || r.contentId || null,
						title: r.title || '',
						year: r.year || '',
						director: r.director || '',
						download: getDownload(r)
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

     loadFilms();
});

//articles loading and rendering
$(function(){
    function renderArticles(items){
		if(!items || items.length === 0){
			if ($('#articles-table').length) {
				$('#articles-table tbody').html('<tr><td colspan="6">Няма намерени статии.</td></tr>');
			} else {
				$("#articles").html("<p>Няма намерени статии.</p>");
			}
			return;
		}

		if ($('#articles-table').length) {
			var isAdmin = userIsAdmin();
			var rows = '';
			$.each(items, function(i, a){
					    rows += '<tr>'
						    + '<td>' + $('<div>').text(a.id).html() + '</td>'
						    + '<td>' + $('<div>').text(a.title).html() + '</td>'
						    + '<td>' + $('<div>').text(a.author || '').html() + '</td>'
						    + '<td>' + $('<div>').text(a.publication || '').html() + '</td>'
						    + '<td>' + $('<div>').text(a.published_date || '').html() + '</td>'
						    + '<td>' + (getDownload(a) ? '<a href="' + $('<div>').text(getDownload(a)).html() + '" target="_blank" rel="noopener">Изтегли</a>' : '') + '</td>';
				    if (isAdmin) rows += '<td><a href="edit.php?content_id=' + encodeURIComponent(a.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(a.id).html() + '">Изтрий</button></td>';
				    rows += '</tr>';
			});
			$('#articles-table tbody').html(rows);
			return;
		}

		var isAdmin = userIsAdmin();
		var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Publication</th><th>Date</th><th>Линк</th>' + (isAdmin?'<th class="actions">Действия</th>':'') + '</tr></thead><tbody>';
		$.each(items, function(i, a){
						html += '<tr>'
							+ '<td>' + $('<div>').text(a.id).html() + '</td>'
							+ '<td>' + $('<div>').text(a.title).html() + '</td>'
							+ '<td>' + $('<div>').text(a.author || '').html() + '</td>'
							+ '<td>' + $('<div>').text(a.publication || '').html() + '</td>'
							+ '<td>' + $('<div>').text(a.published_date || '').html() + '</td>'
							+ '<td>' + (getDownload(a) ? '<a href="' + $('<div>').text(getDownload(a)).html() + '" target="_blank" rel="noopener">Изтегли</a>' : '') + '</td>'
							+ (isAdmin ? '<td><a href="edit.php?content_id=' + encodeURIComponent(a.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(a.id).html() + '">Изтрий</button></td>' : '')
							+ '</tr>';
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
                $("#message-articles").text('Невалиден отговор от сървъра');
                $("#articles").empty();
                return;
            }
            if (response.status && response.status !== 'success') {
                $("#message-articles").text(response.message || 'Грешка при зареждане на статиите');
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
					published_date: r.published_date || r.article_published_date || '',
					download: r.download_link || r.download || ''
                };
            });
            renderArticles(items);
        }).fail(function(jqXHR, textStatus, errorThrown){
            var msg = "Грешка при зареждане на статиите: " + (errorThrown || textStatus);
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
				$(targetSelector + ' table tbody').html('<tr><td colspan="6">Няма намерени книги.</td></tr>');
			} else {
				$(targetSelector).html("<p>Няма намерени книги.</p>");
			}
			return;
		}


		if ($(targetSelector + ' table').length) {
			var rows = '';
			var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
			var isAdmin = (role === 'administrator' || role === 'moderator');
			// only populate tbody for an existing table skeleton
			$.each(items, function(i, b){
				rows += '<tr>'
					  + '<td>' + $('<div>').text(b.id).html() + '</td>'
					  + '<td>' + $('<div>').text(b.title).html() + '</td>'
					  + '<td>' + $('<div>').text(b.author || '').html() + '</td>'
					  + '<td>' + $('<div>').text(b.year || '').html() + '</td>'
					  + '<td>' + (b.download ? '<a href="' + $('<div>').text(b.download).html() + '" target="_blank" rel="noopener">Изтегли</a>' : '') + '</td>';
				if (isAdmin) rows += '<td><a href="edit.php?content_id=' + encodeURIComponent(b.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(b.id).html() + '">Изтрий</button></td>';
				rows += '</tr>';
			});
			$(targetSelector + ' table tbody').html(rows);
			return;
		}

		var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
		var isAdmin = (role === 'administrator' || role === 'moderator');
		var $booksHead = $(targetSelector + ' table thead tr');
		if ($booksHead.length) {
			// ensure download header is inserted before actions if a static actions header exists
			if ($booksHead.find('th.download').length === 0) {
				if ($booksHead.find('th.actions').length) {
					$booksHead.find('th.actions').first().before('<th class="download">Линк</th>');
				} else {
					$booksHead.append('<th class="download">Линк</th>');
				}
			}
			if (isAdmin && $booksHead.find('th.actions').length === 0) {
				$booksHead.append('<th class="actions">Действия</th>');
			}
		}
		var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Year</th><th>Линк</th>' + (isAdmin?'<th class="actions">Действия</th>':'') + '</tr></thead><tbody>';
		$.each(items, function(i, b){
		  html += '<tr>'
			  + '<td>' + $('<div>').text(b.id).html() + '</td>'
			  + '<td>' + $('<div>').text(b.title).html() + '</td>'
			  + '<td>' + $('<div>').text(b.author || '').html() + '</td>'
			  + '<td>' + $('<div>').text(b.year || '').html() + '</td>'
			  + '<td>' + (b.download ? '<a href="' + $('<div>').text(b.download).html() + '" target="_blank" rel="noopener">Изтегли</a>' : '') + '</td>'
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
		if (loader.length === 0) loader = $("<span style='display:none;' id='loader-books'>Зареждане...</span>").insertBefore(target);
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
				message.text('Невалиден отговор от сървъра');
				$(target).empty();
				return;
			}
			if (response.status && response.status !== 'success') {
				message.text(response.message || 'Грешка при зареждане на книгите');
				$(target).empty();
				return;
			}
			var items = response.data || response;
			items = items.map(function(r){
				return {
					id: r.content_id || r.id || null,
					title: r.title || '',
					author: r.author || r.book_author || '',
					year: r.year || '',
					download: r.download_link || r.download || ''
				};
			});
			renderBooks(items, target);
		}).fail(function(jqXHR, textStatus, errorThrown){
			var msg = "Грешка при зареждане на книгите: " + (errorThrown || textStatus);
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
				$('#tv-table tbody').html('<tr><td colspan="6">Няма намерени ТВ сериали.</td></tr>');
			} else {
				$("#tv").html("<p>Няма намерени ТВ сериали.</p>");
			}
			return;
		}

		var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
		var isAdmin = (role === 'administrator' || role === 'moderator');

		if ($('#tv-table').length) {
			var rows = '';
			$.each(items, function(i, t){
				rows += '<tr>'
				  + '<td>' + $('<div>').text(t.id).html() + '</td>'
				  + '<td>' + $('<div>').text(t.title).html() + '</td>'
				  + '<td>' + $('<div>').text(t.seasons || '').html() + '</td>'
				  + '<td>' + $('<div>').text(t.episodes || '').html() + '</td>'
				  + '<td>' + $('<div>').text(t.showrunner || '').html() + '</td>'
				  + '<td>' + (t.download ? '<a href="' + $('<div>').text(t.download).html() + '" target="_blank" rel="noopener">Изтегли</a>' : '') + '</td>';
				if (isAdmin) rows += '<td><a href="edit.php?content_id=' + encodeURIComponent(t.id) + '">Редактирай</a> <button class="delete-btn" data-id="' + $('<div>').text(t.id).html() + '">Изтрий</button></td>';
				rows += '</tr>';
			});
			$('#tv-table tbody').html(rows);
			return;
		}

		var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Seasons</th><th>Episodes</th><th>Showrunner</th><th>Линк</th>' + (isAdmin?'<th class="actions">Действия</th>':'') + '</tr></thead><tbody>';
		$.each(items, function(i, t){
			html += '<tr>'
			  + '<td>' + $('<div>').text(t.id).html() + '</td>'
			  + '<td>' + $('<div>').text(t.title).html() + '</td>'
			  + '<td>' + $('<div>').text(t.seasons || '').html() + '</td>'
			  + '<td>' + $('<div>').text(t.episodes || '').html() + '</td>'
			  + '<td>' + $('<div>').text(t.showrunner || '').html() + '</td>'
			  + '<td>' + (t.download ? '<a href="' + $('<div>').text(t.download).html() + '" target="_blank" rel="noopener">Изтегли</a>' : '') + '</td>'
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
		if (loader.length === 0) loader = $("<span style='display:none;' id='loader-tv'>Зареждане...</span>").insertBefore('#tv');
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
				message.text('Невалиден отговор от сървъра');
				$('#tv').empty();
				return;
			}
			if (response.status && response.status !== 'success') {
				message.text(response.message || 'Грешка при зареждане на ТВ сериалите');
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
					episodes: r.episodes || '',
					download: r.download_link || r.download || ''
				};
			});
			renderTV(items);
		}).fail(function(jqXHR, textStatus, errorThrown){
			var msg = "Грешка при зареждане на ТВ сериалите: " + (errorThrown || textStatus);
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


// AJAX-driven edit form builder for edit.php
$(document).ready(function(){
	var root = $('#edit-root');
	if (!root.length) return;
	var contentId = root.data('content-id');
	var container = $('#edit-form-container');
	if (!contentId) {
		container.html('<p>Невалидно съдържание.</p>');
		return;
	}

	container.html('<p>Зареждане...</p>');

	fetch('api/get_content.php?content_id=' + encodeURIComponent(contentId), { headers: { 'Accept': 'application/json' } })
		.then(function(resp){ return resp.json().catch(function(){ return null; }); })
		.then(function(resp){
			if (!resp || resp.status !== 'success') {
				Swal.fire({ icon: 'error', title: 'Грешка', text: (resp && resp.message) ? resp.message : 'Неуспешно зареждане.' });
				container.html('<p>Неуспешно зареждане.</p>');
				return;
			}

			var content = resp.data.content || {};
			var tr = resp.data.typeRow || {};
			var type = content.type || '';

			// build form
			var form = $("<form id='edit-content-form' enctype='multipart/form-data' method='post'></form>");
			form.append("<input type='hidden' name='content_id' value='" + $('<div>').text(content.id || '').html() + "' />");

			// common fields
			form.append("<label>Заглавие</label>");
			form.append("<input type='text' name='title' value='" + $('<div>').text(content.title || '').html() + "' class='full' />");

			form.append("<label>Описание</label>");
			form.append("<textarea name='description' class='full'>" + $('<div>').text(content.description || '').html() + "</textarea>");

			form.append("<label>Година</label>");
			form.append("<input type='number' name='year' value='" + $('<div>').text(content.year || '').html() + "' />");

			form.append("<label>Жанр</label>");
			form.append("<input type='text' name='genre' value='" + $('<div>').text(content.genre || '').html() + "' />");

			// cover preview + file input
			if (content.cover_path) {
				form.append("<div style='margin:8px 0;'><label>Текуща корица</label><div><img src='" + $('<div>').text(content.cover_path).html() + "' alt='cover' style='max-width:150px;display:block;margin:6px 0;' /></div></div>");
			}
			form.append("<label>Смени корица (по избор)</label>");
			form.append("<input type='file' name='cover_path' accept='image/*' />");

			// type-specific fields (use server field names from server-side template)
			if (type === 'book') {
				form.append("<label>Автор</label>");
				var ba = tr.author || tr.book_author || '';
				form.append("<input type='text' name='book_author' value='" + $('<div>').text(ba).html() + "' />");
			} else if (type === 'film') {
				form.append("<label>Режисьор</label>");
				form.append("<input type='text' name='film_director' value='" + $('<div>').text(tr.director || '').html() + "' />");
				var actorsVal = '';
				if (Array.isArray(tr.actors)) actorsVal = tr.actors.join(', ');
				else if (typeof tr.actors === 'string') actorsVal = tr.actors;
				form.append("<label>Актьори</label>");
				form.append("<input type='text' name='film_actors' value='" + $('<div>').text(actorsVal).html() + "' />");
			} else if (type === 'tv') {
				form.append("<label>Шоурънър</label>");
				form.append("<input type='text' name='tv_showrunner' value='" + $('<div>').text(tr.showrunner || '').html() + "' />");
				form.append("<label>Сезони</label>");
				form.append("<input type='number' name='tv_seasons' value='" + $('<div>').text(tr.seasons || '').html() + "' />");
				form.append("<label>Епизоди</label>");
				form.append("<input type='number' name='tv_episodes' value='" + $('<div>').text(tr.episodes || '').html() + "' />");
			} else if (type === 'article') {
				form.append("<label>Автор</label>");
				form.append("<input type='text' name='article_author' value='" + $('<div>').text(tr.author || tr.article_author || '').html() + "' />");
				form.append("<label>Публикация</label>");
				form.append("<input type='text' name='article_publication' value='" + $('<div>').text(tr.publication || '').html() + "' />");
				form.append("<label>Дата</label>");
				form.append("<input type='date' name='article_published_date' value='" + $('<div>').text(tr.published_date || '').html() + "' />");
			}

			// buttons and optional delete for moderators/administrators
			var role = (document.body && document.body.dataset && document.body.dataset.role) ? document.body.dataset.role : 'guest';
			if (role === 'administrator' || role === 'moderator') {
				form.append("<div style='margin-top:12px;'><button type='submit' class='primary'>Запази</button> <button type='button' class='danger delete-btn' data-id='" + $('<div>').text(content.id || '').html() + "'>Изтрий</button> <a href='index.php' class='button'>Откажи</a></div>");
			} else {
				form.append("<div style='margin-top:12px;'><button type='submit' class='primary'>Запази</button> <a href='index.php' class='button'>Откажи</a></div>");
			}

			container.html('');
			container.append(form);

			// #TODO add delete img button
			var fileInput = form.find("input[name='cover_path']");
			if (fileInput.length) {
				var previewWrap = $("<div class='cover-preview' style='margin-top:6px;'></div>");
				fileInput.after(previewWrap);
				fileInput.on('change', function(){
					var f = this.files && this.files[0];
					previewWrap.empty();
					if (!f) return;
					if (!f.type || f.type.indexOf('image/') !== 0) return;
					var reader = new FileReader();
					reader.onload = function(ev){
						var img = $("<img style='max-width:160px;display:block;margin-top:6px;' />");
						img.attr('src', ev.target.result);
						previewWrap.append(img);
					};
					reader.readAsDataURL(f);
				});
			}

			// submit handler with client-side validation
			form.on('submit', function(e){
				e.preventDefault();
				// basic validation
				var titleVal = $.trim(form.find("input[name='title']").val() || '');
				if (!titleVal) {
					Swal.fire({ icon: 'warning', title: 'Липсва заглавие', text: 'Моля въведете заглавие.' });
					return;
				}
				if (type === 'book') {
					var auth = $.trim(form.find("input[name='book_author']").val() || '');
					if (!auth) { Swal.fire({ icon: 'warning', title: 'Липсва автор', text: 'Моля въведете автор.' }); return; }
				}
				if (type === 'film') {
					var dir = $.trim(form.find("input[name='film_director']").val() || '');
					if (!dir) { Swal.fire({ icon: 'warning', title: 'Липсва режисьор', text: 'Моля въведете режисьор.' }); return; }
				}
				if (type === 'article') {
					var a = $.trim(form.find("input[name='article_author']").val() || '');
					if (!a) { Swal.fire({ icon: 'warning', title: 'Липсва автор', text: 'Моля въведете автор на статията.' }); return; }
				}

				var fd = new FormData(this);
				// use central edit API for all types
				var endpoint = 'api/edit.php';

				fetch(endpoint, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
					.then(function(r){ return r.json().catch(function(){ return null; }); })
					.then(function(d){
						if (d && d.status === 'success') {
							Swal.fire({ icon: 'success', title: d.message || 'Успешно', timer: 1000, showConfirmButton: false }).then(function(){
								// reload current page to reflect saved changes (keeps user on edit view)
								location.reload();
							});
						} else {
							Swal.fire({ icon: 'error', title: 'Грешка', text: (d && d.message) ? d.message : 'Неуспешно запазване.' });
						}
					}).catch(function(err){
						console.error(err);
						Swal.fire({ icon: 'error', title: 'Грешка', text: 'Грешка при изпращане.' });
					});
			});

		}).catch(function(err){
			console.error(err);
			Swal.fire({ icon: 'error', title: 'Грешка', text: 'Неуспешно свързване със сървъра.' });
			container.html('<p>Неуспешно зареждане.</p>');
		});

});

