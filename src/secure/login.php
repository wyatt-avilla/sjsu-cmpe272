<?php
require_once __DIR__ . '/auth.php';

$redirect_param = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
$redirect_param_value = is_array($redirect_param) ? '' : trim((string) $redirect_param);
$redirect = auth_safe_redirect($redirect_param_value, 'users.php');
$redirect_was_requested = $redirect_param_value !== '' && $redirect === $redirect_param_value;
$redirect_input_value = $redirect_was_requested ? htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') : '';
$create_account_url = '/term_project/create_account.php?redirect=' . rawurlencode($redirect);
$show_create_account = $redirect === '/term_project' || strpos($redirect, '/term_project/') === 0;

if (auth_is_logged_in()) {
	if (!$redirect_was_requested && !auth_is_admin()) {
		$redirect = '/';
	}

	header('Location: ' . $redirect);
	exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$password = $_POST['password'] ?? '';

	if (auth_login($username, $password)) {
		if (!$redirect_was_requested && !auth_is_admin()) {
			$redirect = '/';
		}

		header('Location: ' . $redirect);
		exit;
	} else {
		$error = 'Invalid username or password';
	}
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<link rel="stylesheet" href="/term_project/styles.css">
</head>
<body class="tp-page">
	<header class="tp-header">
		<div class="tp-shell tp-header-inner">
			<h1 class="tp-brand">Login</h1>
			<?php if ($show_create_account): ?>
				<nav class="tp-nav" aria-label="Login navigation">
					<a href="/term_project/">Marketplace</a>
					<a href="<?php echo htmlspecialchars($create_account_url, ENT_QUOTES, 'UTF-8'); ?>">Create Account</a>
				</nav>
			<?php endif; ?>
		</div>
	</header>

	<main class="tp-main">
		<div class="tp-shell">
			<?php if ($error): ?>
				<p class="tp-message tp-message-error"><?php echo htmlspecialchars($error); ?></p>
			<?php endif; ?>

			<form class="tp-form tp-form-narrow tp-panel" method="post" action="login.php">
				<input type="hidden" name="redirect" value="<?php echo $redirect_input_value; ?>">

				<div class="tp-field">
					<label for="username">Username</label>
					<input type="text" id="username" name="username" required>
				</div>

				<div class="tp-field">
					<label for="password">Password</label>
					<input type="password" id="password" name="password" required>
				</div>

				<div>
					<button class="tp-submit" type="submit">Login</button>
				</div>
			</form>

			<?php if ($show_create_account): ?>
				<p class="tp-auth-link">Need an account? <a href="<?php echo htmlspecialchars($create_account_url, ENT_QUOTES, 'UTF-8'); ?>">Create Account</a></p>
			<?php endif; ?>
		</div>
	</main>
</body>
</html>
