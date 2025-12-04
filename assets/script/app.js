document.addEventListener('DOMContentLoaded', function () {
	// Find the login form that posts to api/login.php
	const loginForm = document.querySelector('form[action="api/login.php"]');
	if (!loginForm) return;

	// Insert a place for inline messages
	let msgBox = document.createElement('div');
	msgBox.className = 'form-message';
	msgBox.style.color = 'red';
	msgBox.style.marginBottom = '0.5rem';
	loginForm.insertBefore(msgBox, loginForm.firstChild);

	loginForm.addEventListener('submit', async function (e) {
		e.preventDefault();
		msgBox.textContent = '';

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
				msgBox.textContent = text || `Invalid JSON response (status ${resp.status} ${resp.statusText})`;
				return;
			}

			if (data.status === 'success') {
				// Redirect to index.php with message
				const msg = encodeURIComponent(data.message || 'Успешен вход');
				window.location.href = 'index.php?msg=' + msg;
			} else {
				msgBox.textContent = data.message || 'Грешка при вход.';
			}
		} catch (err) {
			console.error(err);
			msgBox.textContent = 'Грешка при изпращане. Моля опитайте отново.';
		}
	});
});

document.addEventListener('DOMContentLoaded', function () {
    const regForm = document.querySelector('form[action="api/register.php"]');
    if (!regForm) return;

    let msgBox = document.createElement('div');
    msgBox.className = 'form-message';
    msgBox.style.color = 'red';
    msgBox.style.marginBottom = '0.5rem';
    regForm.insertBefore(msgBox, regForm.firstChild);

    regForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        msgBox.textContent = '';

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
                msgBox.textContent = text || `Invalid JSON response (status ${resp.status} ${resp.statusText})`;
                return;
            }

			if (data.status === 'success') {
				const msg = encodeURIComponent(data.message || 'Регистрацията е успешна');
				// After registration send user to login page with message
				window.location.href = 'log_in.php?msg=' + msg;
			} else {
                msgBox.textContent = data.message || 'Грешка при регистрация.';
            }
        } catch (err) {
            console.error(err);
            msgBox.textContent = 'Грешка при изпращане. Моля опитайте отново.';
        }
    });
});