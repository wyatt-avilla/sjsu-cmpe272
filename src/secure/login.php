<?php
session_start();
require_once 'get_db.php';

if (isset($_SESSION['user_id'])) {
	header('Location: users.php');
	exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = $_POST['username'];
	$password = $_POST['password'];

	$pdo = get_db();

	$stmt = $pdo->prepare('SELECT * FROM users WHERE user_name = ?');
	$stmt->execute([$username]);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($user && password_verify($password, $user['password_hash'])) {
		$_SESSION['user_id'] = $user['user_id'];
		$_SESSION['user_name'] = $user['user_name'];
		$_SESSION['is_admin'] = $user['is_admin'];
		header('Location: users.php');
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
