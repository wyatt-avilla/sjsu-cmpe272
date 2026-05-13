<?php
require_once __DIR__ . '/get_db.php';

function auth_start_session() {
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
}

function auth_login($username, $password) {
	auth_start_session();

	$pdo = get_db();

	$stmt = $pdo->prepare('SELECT * FROM users WHERE user_name = ?');
	$stmt->execute([$username]);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$user || !password_verify($password, (string) ($user['password_hash'] ?? ''))) {
		return false;
	}

	session_regenerate_id(true);
	$_SESSION['user_id'] = $user['user_id'];
	$_SESSION['user_name'] = $user['user_name'];
	$_SESSION['is_admin'] = (bool) $user['is_admin'];

	return true;
}

function auth_logout() {
	auth_start_session();
	$_SESSION = [];

	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(
			session_name(),
			'',
			time() - 42000,
			$params['path'],
			$params['domain'],
			$params['secure'],
			$params['httponly']
		);
	}

	session_destroy();
}

function auth_is_logged_in() {
	auth_start_session();

	return isset($_SESSION['user_id']);
}

function auth_is_admin() {
	auth_start_session();

	return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

function auth_current_user_name() {
	auth_start_session();

	return $_SESSION['user_name'] ?? null;
}

function auth_require_admin($login_path = '/secure/login.php') {
	if (!auth_is_logged_in() || !auth_is_admin()) {
		header('Location: ' . $login_path);
		exit;
	}
}

function auth_safe_redirect($value, $default) {
	if (is_array($value)) {
		return $default;
	}

	$value = trim((string) $value);

	if ($value === '' || preg_match('/[\r\n]/', $value)) {
		return $default;
	}

	if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $value) || strpos($value, '//') === 0) {
		return $default;
	}

	return $value;
}
