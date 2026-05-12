<?php
require_once __DIR__ . '/auth.php';

$redirect_param = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
$redirect_param_value = is_array($redirect_param) ? '' : trim((string) $redirect_param);
$redirect = auth_safe_redirect($redirect_param_value, 'users.php');
$redirect_was_requested = $redirect_param_value !== '' && $redirect === $redirect_param_value;
$redirect_input_value = $redirect_was_requested ? htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') : '';

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
<html>
<head>
	<title>Login</title>
</head>
<body>
	<h1>Login</h1>
	<form method="post" action="login.php">
		<input type="hidden" name="redirect" value="<?php echo $redirect_input_value; ?>">
		<label for="username">Username:</label>
		<input type="text" id="username" name="username" required><br><br>
		<label for="password">Password:</label>
		<input type="password" id="password" name="password" required><br><br>
		<input type="submit" value="Login">
	</form>

	<?php if ($error): ?>
		<p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
	<?php endif; ?>
</html>
