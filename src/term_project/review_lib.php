<?php
require_once __DIR__ . '/../secure/get_db.php';

function term_project_review_product_key($company_name, $product_link) {
	return sha1(trim((string) $company_name) . "\0" . trim((string) $product_link));
}

function term_project_get_reviewable_products($companies) {
	$products = [];

	foreach ($companies as $company) {
		if (empty($company['products']) || !is_array($company['products'])) {
			continue;
		}

		$company_name = trim((string) ($company['company_name'] ?? 'Unknown Company'));

		foreach ($company['products'] as $product) {
			if (!is_array($product)) {
				continue;
			}

			$product_name = trim((string) ($product['title'] ?? ''));
			$product_link = trim((string) ($product['product_link'] ?? ''));

			if ($product_name === '' || $product_link === '') {
				continue;
			}

			$key = term_project_review_product_key($company_name, $product_link);

			if (isset($products[$key])) {
				continue;
			}

			$products[$key] = [
				'company_name' => $company_name,
				'product_name' => $product_name,
				'product_link' => $product_link,
			];
		}
	}

	uasort($products, function ($left, $right) {
		$company = strcasecmp($left['company_name'], $right['company_name']);

		if ($company !== 0) {
			return $company;
		}

		return strcasecmp($left['product_name'], $right['product_name']);
	});

	return $products;
}

function term_project_get_product_average_ratings() {
	try {
		$pdo = get_db();
		$stmt = $pdo->query(
			'SELECT company_name, product_link, AVG(rating) AS average_rating
			FROM product_review
			GROUP BY company_name, product_link'
		);
		$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (Throwable $e) {
		return [];
	}

	$ratings = [];

	foreach ($rows as $row) {
		$company_name = trim((string) ($row['company_name'] ?? ''));
		$product_link = trim((string) ($row['product_link'] ?? ''));

		if ($company_name === '' || $product_link === '') {
			continue;
		}

		$ratings[term_project_review_product_key($company_name, $product_link)] = [
			'average_rating' => is_numeric($row['average_rating'] ?? null) ? (float) $row['average_rating'] : null,
		];
	}

	return $ratings;
}

function term_project_get_average_rating_for_product($company_name, $product_link, $average_ratings) {
	$key = term_project_review_product_key($company_name, $product_link);

	return $average_ratings[$key] ?? null;
}

function term_project_format_average_rating($average_rating) {
	if (empty($average_rating) || !isset($average_rating['average_rating'])) {
		return 'No ratings yet';
	}

	$rating = max(0, min(5, (float) $average_rating['average_rating']));
	$filled_stars = (int) round($rating);

	return str_repeat('&#9733;', $filled_stars)
		. str_repeat('&#9734;', 5 - $filled_stars)
		. ' '
		. number_format($rating, 1)
		. '/5 average';
}

function term_project_insert_product_review($user_id, $product, $rating, $review_text) {
	$pdo = get_db();
	$stmt = $pdo->prepare(
		'INSERT INTO product_review (user_id, company_name, product_name, product_link, rating, review_text)
		VALUES (?, ?, ?, ?, ?, ?)'
	);

	$stmt->execute([
		$user_id,
		$product['company_name'],
		$product['product_name'],
		$product['product_link'],
		$rating,
		$review_text,
	]);
}
