
<!--login page. add capcha? store session. check user role on login -->

<?php
session_start();

if (isset($_SESSION["user_id"])) {
	header("Location: index.php");
	exit;
}
require_once __DIR__ . '/config.php';

// Optional flash message (e.g. after successful registration)
$flash_msg = '';
if (!empty($_GET['msg'])) {
	$flash_msg = htmlspecialchars($_GET['msg'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Вход — Български Културен Архив</title>
	<link rel="stylesheet" href="assets/style.css">
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

	<main>
		<section class="hero">
			<div class="container">
				<?php if (!empty($flash_msg)): ?>
				<div class="flash-success" style="background:#e6ffed;border:1px solid #b6f1c5;padding:0.75rem;border-radius:4px;color:#065f26;margin-bottom:1rem;">
					<?php echo $flash_msg; ?>
				</div>
				<?php endif; ?>
				<div class="card auth-card">
					<div class="auth-forms">
						<!-- Login panel -->
						<div class="panel login">
							<div >
								<h2>Вход</h2>
								<form action="api/login.php" method="POST">
									<label for="log-emailOrUsername">Имейл или потребителско име</label>
									<input id="log-emailOrUsername" name="log-emailOrUsername" type="text" required>

									<label for="log-password">Парола</label>
									<input id="log-password" name="log-password" type="password" required>

									<div>
										<label for="log-remember">Запомни ме</label>
										<input type="checkbox" id="log-remember" name="log-remember" value="1">
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
