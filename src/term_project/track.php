<?php
require_once __DIR__ . '/../secure/auth.php';
require_once __DIR__ . '/../secure/get_db.php';

$company = $_GET['company'] ?? '';
$product = $_GET['product'] ?? '';
$link = $_GET['link'] ?? '';

if ($company === '' || $product === '' || $link === '') {
	header('Location: /term_project/');
	exit;
}

$user_id = auth_current_user_id();
$pdo = get_db();

if ($user_id !== null) {
	$stmt = $pdo->prepare('
		INSERT INTO product_tracking (user_id, company_name, product_name, product_link, click_count)
		VALUES (?, ?, ?, ?, 1)
		ON DUPLICATE KEY UPDATE click_count = click_count + 1
	');
	$stmt->execute([$user_id, $company, $product, $link]);
} else {
	// Handle anonymous users
	$stmt = $pdo->prepare('
		SELECT product_tracking_id FROM product_tracking 
		WHERE user_id IS NULL AND company_name = ? AND product_name = ?
	');
	$stmt->execute([$company, $product]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		$stmt = $pdo->prepare('
			UPDATE product_tracking 
			SET click_count = click_count + 1 
			WHERE product_tracking_id = ?
		');
		$stmt->execute([$row['product_tracking_id']]);
	} else {
		$stmt = $pdo->prepare('
			INSERT INTO product_tracking (user_id, company_name, product_name, product_link, click_count)
			VALUES (NULL, ?, ?, ?, 1)
		');
		$stmt->execute([$company, $product, $link]);
	}
}

header('Location: ' . $link);
exit;
