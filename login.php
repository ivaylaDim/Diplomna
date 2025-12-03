<?php
?>
<!DOCTYPE html>
<html lang="bg">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Вход — Български Културен Архив</title>
	<link rel="stylesheet" href="assets/style.css">
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
					<li><a href="/DIPLOMNA/">Начало</a></li>
					<li><a href="register.php">Регистрация</a></li>
				</ul>
			</nav>
			<div class="user-actions">
				<a href="login.php" class="btn-login">Вход</a>
				<a href="register.php" class="btn-register">Регистрация</a>
			</div>
		</div>
	</header>

	<main>
		<section class="hero">
			<div class="container">
				<div class="card auth-card">
					<div class="auth-forms">
						<!-- Login panel -->
						<div class="panel login">
							<div>
								<h2>Вход</h2>
								<form action="api/login.php" method="POST">
									<label for="email">Имейл</label>
									<input id="email" name="email" type="email" required>

									<label for="password">Парола</label>
									<input id="password" name="password" type="password" required>

									<div class="form-row">
										<label><input type="checkbox" name="remember"> Запомни ме</label>
										<a href="register.php" id="show-register">Нямате акаунт?</a>
									</div>

									<button type="submit" class="btn-primary">Вход</button>
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
