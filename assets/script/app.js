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

document.addEventListener('DOMContentLoaded', function () {
    const regForm = document.querySelector('form[action="api/register.php"]');
    if (!regForm) return;


    regForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(regForm);

        try {
            const resp = await fetch(regForm.action, {
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

document.getElementById('logoutBtn').addEventListener('click', function (e) {
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
