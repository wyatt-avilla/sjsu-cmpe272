<?php
require_once __DIR__ . '/../secure/auth.php';
require_once __DIR__ . '/../secure/get_db.php';

auth_start_session();

if (!auth_is_logged_in()) {
	header('Location: /secure/login.php?redirect=/term_project/tracking.php');
	exit;
}

$pdo = get_db();
$is_admin = auth_is_admin();
$user_id = auth_current_user_id();

if ($is_admin) {
	$stmt = $pdo->query('
		SELECT pt.*, u.user_name 
		FROM product_tracking pt
		LEFT JOIN users u ON pt.user_id = u.user_id
		ORDER BY pt.click_count DESC, pt.clicked_at DESC
	');
	$tracking_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
	$stmt = $pdo->prepare('
		SELECT pt.*, u.user_name 
		FROM product_tracking pt
		LEFT JOIN users u ON pt.user_id = u.user_id
		WHERE pt.user_id = ?
		ORDER BY pt.click_count DESC, pt.clicked_at DESC
	');
	$stmt->execute([$user_id]);
	$tracking_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function escape_html($value) {
	return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Tracking Statistics</title>
	<style>
		table { border-collapse: collapse; width: 100%; margin-top: 20px; }
		th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
		th { background-color: #f4f4f4; }
	</style>
</head>
<body>
	<header>
		<a href="/term_project/">&larr; Back to Market Place</a>
		<h1>Tracking Statistics</h1>
		<p>Signed in as <?php echo escape_html(auth_current_user_name()); ?> <?php if ($is_admin) echo '(Admin)'; ?></p>
	</header>

	<main>
		<?php if (empty($tracking_data)): ?>
			<p>No tracking data available.</p>
		<?php else: ?>
			<table>
				<thead>
					<tr>
						<th>User</th>
						<th>Company</th>
						<th>Product</th>
						<th>Link</th>
						<th>Clicks</th>
						<th>Last Clicked</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($tracking_data as $row): ?>
						<tr>
							<td>
								<?php
								if ($row['user_id'] === null || $row['user_id'] === '') {
									echo '<em>Anonymous</em>';
								} else {
									echo escape_html($row['user_name'] ?? 'Unknown User');
									if ($is_admin) {
										echo ' <span style="color:#666;">(ID: ' . escape_html((string) $row['user_id']) . ')</span>';
									}
								}
								?>
							</td>
							<td><?php echo escape_html($row['company_name']); ?></td>
							<td><?php echo escape_html($row['product_name']); ?></td>
							<td><a href="<?php echo escape_html($row['product_link']); ?>" target="_blank">Link</a></td>
							<td><?php echo escape_html($row['click_count']); ?></td>
							<td><?php echo escape_html($row['clicked_at']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</main>
</body>
</html>
