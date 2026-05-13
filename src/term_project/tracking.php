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
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Tracking Statistics</title>
	<link rel="stylesheet" href="/term_project/styles.css">
</head>
<body class="tp-page">
	<header class="tp-header">
		<div class="tp-shell tp-header-inner">
			<h1 class="tp-brand">Tracking Statistics</h1>
			<nav class="tp-nav" aria-label="Tracking navigation">
				<a href="/term_project/">Back to Market Place</a>
				<span>Signed in as <?php echo escape_html(auth_current_user_name()); ?> <?php if ($is_admin) echo '(Admin)'; ?></span>
			</nav>
		</div>
	</header>

	<main class="tp-main">
		<div class="tp-shell">
			<?php if (empty($tracking_data)): ?>
				<p class="tp-empty">No tracking data available.</p>
			<?php else: ?>
				<div class="tp-table-wrap">
					<table class="tp-table">
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
											echo ' <span class="tp-user-id">(ID: ' . escape_html((string) $row['user_id']) . ')</span>';
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
				</div>
			<?php endif; ?>
		</div>
	</main>
</body>
</html>
