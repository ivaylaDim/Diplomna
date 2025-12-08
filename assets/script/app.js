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
				// Redirect to index.php with message
					const msgText = data.message || 'Успешен вход';
					Swal.fire({
						icon: 'success',
						title: msgText,
						timer: 1200,
						showConfirmButton: false
					}).then(() => {
						window.location.href = 'index.php?msg=' + encodeURIComponent(msgText);
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
				}).then(() => {
					window.location.href = 'log_in.php?msg=' + encodeURIComponent(msgText);
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


// Submission Form Handler
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
				}).then(() => {
					window.location.href = 'index.php?msg=' + encodeURIComponent(msgText);
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

$(document).ready(function () {
            // Handle content type change
            $('#content-type').on('change', function () {
                const selectedType = $(this).val();
                
                // Hide all type-specific fieldsets
                $('.type-fields').hide();
                
                // Show the selected type's fieldset
                if (selectedType) {
                    $('#fields-' + selectedType).show();
                }
            });
        });

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


$(function(){
     function renderTable(items){
          if(!items || items.length === 0){
                $("#films").html("<p>No films found.</p>");
                return;
          }
          var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Year</th><th>Director</th></tr></thead><tbody>';
          $.each(items, function(i, f){
                html += '<tr>'
                      + '<td>' + $('<div>').text(f.id).html() + '</td>'
                      + '<td>' + $('<div>').text(f.title).html() + '</td>'
                      + '<td>' + $('<div>').text(f.year).html() + '</td>'
                      + '<td>' + $('<div>').text(f.director).html() + '</td>'
                      + '</tr>';
          });
          html += '</tbody></table>';
          $("#films").html(html);
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
				renderTable(items);
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


(function(){
    function renderArticles(items){
        if(!items || items.length === 0){
            $("#articles").html("<p>No articles found.</p>");
            return;
        }
        var html = '<table><thead><tr><th>ID</th><th>Title</th><th>Author</th><th>Publication</th><th>Date</th></tr></thead><tbody>';
        $.each(items, function(i, a){
            html += '<tr>'
                  + '<td>' + $('<div>').text(a.id).html() + '</td>'
                  + '<td>' + $('<div>').text(a.title).html() + '</td>'
                  + '<td>' + $('<div>').text(a.author || '').html() + '</td>'
                  + '<td>' + $('<div>').text(a.publication || '').html() + '</td>'
                  + '<td>' + $('<div>').text(a.published_date || '').html() + '</td>'
                  + '</tr>';
        });
        html += '</tbody></table>';
        $("#articles").html(html);
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
            // Normalize items to expected fields
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
})();