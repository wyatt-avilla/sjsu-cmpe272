<?php

define('ICEDANCER_COMPANY_NAME', 'Icedancer Snow Equipment');

function product_tracking_get_db() {
	if (!is_readable(__DIR__ . '/../.env')) {
		return null;
	}

	require_once __DIR__ . '/../secure/get_db.php';

	try {
		return @get_db();
	} catch (Throwable $e) {
		return null;
	}
}

function track_icedancer_product_visit($product_name, $product_link) {
	$product_name = trim((string) $product_name);
	$product_link = trim((string) $product_link);

	if ($product_name === '' || $product_link === '') {
		return;
	}

	$pdo = product_tracking_get_db();

	if ($pdo === null) {
		return;
	}

	try {
		$stmt = $pdo->prepare(
			'INSERT INTO product_tracking (company_name, product_name, product_link) VALUES (?, ?, ?)'
		);
		$stmt->execute([ICEDANCER_COMPANY_NAME, $product_name, $product_link]);
	} catch (Throwable $e) {
		return;
	}
}

function get_icedancer_most_visited_products($limit = 5) {
	$limit = max(1, min(50, (int) $limit));
	$pdo = product_tracking_get_db();

	if ($pdo === null) {
		return [];
	}

	try {
		$stmt = $pdo->prepare(
			'SELECT product_name AS title, product_link, COUNT(*) AS visit_count
			FROM product_tracking
			WHERE company_name = ?
			GROUP BY product_name, product_link
			ORDER BY visit_count DESC, product_name ASC
			LIMIT ' . $limit
		);
		$stmt->execute([ICEDANCER_COMPANY_NAME]);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (Throwable $e) {
		return [];
	}

	$products = [];

	foreach ($rows as $row) {
		$title = trim((string) ($row['title'] ?? ''));
		$product_link = trim((string) ($row['product_link'] ?? ''));

		if ($title === '' || $product_link === '') {
			continue;
		}

		$products[] = [
			'title' => $title,
			'product_link' => $product_link,
			'visit_count' => is_numeric($row['visit_count'] ?? null) ? (int) $row['visit_count'] : 0,
		];
	}

	return $products;
}
