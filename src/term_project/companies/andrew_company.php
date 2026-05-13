<?php

function andrew_company_get_data() {
	$api_url = getenv('ANDREW_COMPANY_API_URL') ?: 'https://hyunseungsong.com/api/services.php';
	$data = andrew_company_empty_data();
	$response = andrew_company_fetch_url($api_url);

	if ($response !== '') {
		$decoded = json_decode($response, true);

		if (is_array($decoded)) {
			$data['products'] = array_values(array_filter(array_map('andrew_company_normalize_product', $decoded)));
		}
	}

	$data['top_products'] = andrew_company_get_most_visited_products();

	return $data;
}

function andrew_company_get_most_visited_products() {
	$api_url = getenv('ANDREW_COMPANY_TOP_SERVICES_API_URL') ?: 'https://hyunseungsong.com/api/top_services.php';
	$response = andrew_company_fetch_url($api_url);

	if ($response === '') {
		return [];
	}

	$decoded = json_decode($response, true);

	if (!is_array($decoded)) {
		return [];
	}

	$products = array_values(array_filter(array_map('andrew_company_normalize_most_visited_product', $decoded)));

	usort($products, function ($left, $right) {
		$visits = ($right['visit_count'] ?? 0) <=> ($left['visit_count'] ?? 0);

		if ($visits !== 0) {
			return $visits;
		}

		return strcasecmp($left['title'] ?? '', $right['title'] ?? '');
	});

	return array_slice($products, 0, 5);
}

function andrew_company_fetch_url($url) {
	if (function_exists('curl_init')) {
		$ch = curl_init($url);

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_USERAGENT => 'AndrewCompanyCatalog/1.0',
			CURLOPT_HTTPHEADER => ['Accept: application/json'],
		]);

		$body = curl_exec($ch);
		$status_code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

		if (is_string($body) && $status_code >= 200 && $status_code < 300) {
			return $body;
		}
	}

	$context = stream_context_create([
		'http' => [
			'header' => "Accept: application/json\r\nUser-Agent: AndrewCompanyCatalog/1.0\r\n",
			'ignore_errors' => true,
			'timeout' => 10,
		],
	]);
	$body = @file_get_contents($url, false, $context);

	return is_string($body) ? $body : '';
}

function andrew_company_normalize_product($product) {
	if (!is_array($product)) {
		return null;
	}

	$title = trim((string) ($product['title'] ?? ''));
	$product_link = andrew_company_absolute_url(trim((string) ($product['product_link'] ?? '')));

	if ($title === '' || $product_link === '') {
		return null;
	}

	return [
		'title' => $title,
		'description' => trim((string) ($product['description'] ?? '')),
		'price' => is_numeric($product['price'] ?? null) ? (float) $product['price'] : null,
		'image_link' => andrew_company_absolute_url(trim((string) ($product['image_link'] ?? ''))),
		'product_link' => $product_link,
	];
}

function andrew_company_normalize_most_visited_product($product) {
	if (!is_array($product)) {
		return null;
	}

	$normalized = andrew_company_normalize_product($product);

	if ($normalized === null) {
		return null;
	}

	$normalized['visit_count'] = is_numeric($product['visit_count'] ?? null) ? (int) $product['visit_count'] : 0;

	return $normalized;
}

function andrew_company_absolute_url($path) {
	if ($path === '' || preg_match('/^https?:\/\//i', $path)) {
		return $path;
	}

	return 'https://hyunseungsong.com/' . ltrim($path, '/');
}

function andrew_company_empty_data() {
	return [
		'company_name' => 'RIFTMIND',
		'products' => [],
	];
}
