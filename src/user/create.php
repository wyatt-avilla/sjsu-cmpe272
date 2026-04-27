<?php
require_once __DIR__ . '/../secure/get_db.php';

$errors = [];
$values = [
	'user_name' => '',
	'first_name' => '',
	'last_name' => '',
	'email' => '',
	'home_address' => '',
	'home_phone' => '',
	'cell_phone' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	foreach ($values as $field => $_) {
		$values[$field] = trim($_POST[$field] ?? '');
	}

	$password = $_POST['password'] ?? '';

	foreach ($values as $field => $value) {
		if ($value === '') {
			$errors[$field] = 'This field is required.';
		}
	}

	if ($password === '') {
		$errors['password'] = 'This field is required.';
	}

	if ($values['email'] !== '' && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
		$errors['email'] = 'Enter a valid email address.';
	}

	if (!$errors) {
		$pdo = get_db();

		$stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE user_name = ?');
		$stmt->execute([$values['user_name']]);

		if ((int) $stmt->fetchColumn() > 0) {
			$errors['user_name'] = 'Username already exists.';
		} else {
			$stmt = $pdo->prepare(
				'INSERT INTO users (user_name, password_hash, first_name, last_name, email, home_address, home_phone, cell_phone)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
			);
			$stmt->execute([
				$values['user_name'],
				password_hash($password, PASSWORD_BCRYPT),
				$values['first_name'],
				$values['last_name'],
				$values['email'],
				$values['home_address'],
				$values['home_phone'],
				$values['cell_phone'],
			]);

			header('Location: create.php?created=1');
			exit;
		}
	}
}

function h($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Create User</title>
</head>
<body>
	<h1>Create User</h1>

	<?php if (isset($_GET['created'])): ?>
		<p style="color: green;">User created successfully.</p>
	<?php endif; ?>

	<?php if ($errors): ?>
		<p style="color: red;">Please fix the errors below.</p>
	<?php endif; ?>

	<form method="post" action="create.php">
		<label for="user_name">Username:</label>
		<input type="text" id="user_name" name="user_name" value="<?php echo h($values['user_name']); ?>" required>
		<?php if (isset($errors['user_name'])): ?><span style="color: red;"><?php echo h($errors['user_name']); ?></span><?php endif; ?>
		<br><br>

		<label for="password">Password:</label>
		<input type="password" id="password" name="password" required>
		<?php if (isset($errors['password'])): ?><span style="color: red;"><?php echo h($errors['password']); ?></span><?php endif; ?>
		<br><br>

		<label for="first_name">First Name:</label>
		<input type="text" id="first_name" name="first_name" value="<?php echo h($values['first_name']); ?>" required>
		<?php if (isset($errors['first_name'])): ?><span style="color: red;"><?php echo h($errors['first_name']); ?></span><?php endif; ?>
		<br><br>

		<label for="last_name">Last Name:</label>
		<input type="text" id="last_name" name="last_name" value="<?php echo h($values['last_name']); ?>" required>
		<?php if (isset($errors['last_name'])): ?><span style="color: red;"><?php echo h($errors['last_name']); ?></span><?php endif; ?>
		<br><br>

		<label for="email">Email:</label>
		<input type="email" id="email" name="email" value="<?php echo h($values['email']); ?>" required>
		<?php if (isset($errors['email'])): ?><span style="color: red;"><?php echo h($errors['email']); ?></span><?php endif; ?>
		<br><br>

		<label for="home_address">Home Address:</label>
		<input type="text" id="home_address" name="home_address" value="<?php echo h($values['home_address']); ?>" required>
		<?php if (isset($errors['home_address'])): ?><span style="color: red;"><?php echo h($errors['home_address']); ?></span><?php endif; ?>
		<br><br>

		<label for="home_phone">Home Phone:</label>
		<input type="text" id="home_phone" name="home_phone" value="<?php echo h($values['home_phone']); ?>" required>
		<?php if (isset($errors['home_phone'])): ?><span style="color: red;"><?php echo h($errors['home_phone']); ?></span><?php endif; ?>
		<br><br>

		<label for="cell_phone">Cell Phone:</label>
		<input type="text" id="cell_phone" name="cell_phone" value="<?php echo h($values['cell_phone']); ?>" required>
		<?php if (isset($errors['cell_phone'])): ?><span style="color: red;"><?php echo h($errors['cell_phone']); ?></span><?php endif; ?>
		<br><br>

		<input type="submit" value="Create User">
	</form>

	<p><a href="user.php">Back to User</a></p>
</body>
</html>
