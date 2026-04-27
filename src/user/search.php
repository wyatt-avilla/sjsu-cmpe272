<?php
require_once __DIR__ . '/../secure/get_db.php';

$query = trim($_GET['q'] ?? '');
$users = [];

if ($query !== '') {
	$pdo = get_db();
	$like = '%' . $query . '%';
	$stmt = $pdo->prepare(
		"SELECT user_id, user_name, first_name, last_name, email, home_address, home_phone, cell_phone
		FROM users
		WHERE first_name LIKE ?
			OR last_name LIKE ?
			OR CONCAT(first_name, ' ', last_name) LIKE ?
			OR email LIKE ?
			OR home_phone LIKE ?
			OR cell_phone LIKE ?
		ORDER BY last_name, first_name, user_id"
	);
	$stmt->execute([$like, $like, $like, $like, $like, $like]);
	$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function h($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Search Users</title>
</head>
<body>
	<h1>Search Users</h1>

	<form method="get" action="search.php">
		<label for="q">Name, email, or phone:</label>
		<input type="search" id="q" name="q" value="<?php echo h($query); ?>" required>
		<input type="submit" value="Search">
	</form>

	<?php if ($query !== ''): ?>
		<h2>Results for "<?php echo h($query); ?>"</h2>

		<?php if ($users): ?>
			<table border="1" cellpadding="6" cellspacing="0">
				<thead>
					<tr>
						<th>Username</th>
						<th>Name</th>
						<th>Email</th>
						<th>Home Address</th>
						<th>Home Phone</th>
						<th>Cell Phone</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($users as $user): ?>
						<tr>
							<td><?php echo h($user['user_name']); ?></td>
							<td><?php echo h($user['first_name'] . ' ' . $user['last_name']); ?></td>
							<td><?php echo h($user['email']); ?></td>
							<td><?php echo h($user['home_address']); ?></td>
							<td><?php echo h($user['home_phone']); ?></td>
							<td><?php echo h($user['cell_phone']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else: ?>
			<p>No users found.</p>
		<?php endif; ?>
	<?php endif; ?>

	<p><a href="user.php">Back to User</a></p>
</body>
</html>
