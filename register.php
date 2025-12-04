<!-- register form. fields according to db - username, name (optional), email, password (hashed), role (for debug only) -->

<?php
session_start();

if (isset($_SESSION["user_id"])) {
	header("Location: index.php");
	exit;
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Вход — Български Културен Архив</title>
	<link rel="stylesheet" href="assets/style/style.css">
    <script src="assets/script/app.js" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
				<div class="card auth-card">
					<div class="auth-forms"></div>
						<!-- Register panel -->
						<div class="panel register">
							<div>
								<h2>Регистрация</h2>
								<form action="api/register.php" method="POST">
                                    <label for="reg-username">Потребителско име</label>
                                    <input id="reg-username" name="reg-username" type="text" required>
									<label for="reg-name">Име</label>
									<input id="reg-name" name="reg-name" type="text">
									<label for="reg-email">Имейл</label>
									<input id="reg-email" name="reg-email" type="email" required>
									<label for="reg-password">Парола</label>
									<input id="reg-password" name="reg-password" type="password" required>
                                    <label for="reg-password-repeat">Потвърдете паролата</label>
                                    <input id="reg-password-repeat" name="reg-password-repeat" type="password" required>
                                    <label>Роля (за тестване)</label>
                                    <div class="radio-group">
                                        <input type="radio" id="reg-role-user" name="reg-role" value="user" required checked>
                                        <label for="reg-role-user">Потребител</label>

                                        <input type="radio" id="reg-role-moderator" name="reg-role" value="moderator">
                                        <label for="reg-role-moderator">Модератор</label>

                                        <input type="radio" id="reg-role-admin" name="reg-role" value="admin">
                                        <label for="reg-role-admin">Админ</label>
                                    </div>

									<div>
										<a href="log_in.php" id="show-login" style="font-size:0.9rem;color:var(--muted);text-decoration:none">Вече имате акаунт?</a>
									</div>

									<button type="submit" class="btn-register" style="width:100%">Регистрирай се</button>
								</form>
							</div>
						</div>
                    </div>  
                </div>
            </div>
        </section>
	</main>

	<footer class="site-footer">
		<div class="container">
			<div class="footer-bottom">
				<p>&copy; 2024 Български Културен Архив</p>
			</div>
		</div>
	</footer>
</body>
</html>