<?php

function lucas_company_get_data() {
	$api_url = getenv('LUCAS_COMPANY_API_URL') ?: 'https://pap.uncannydev.com/api/company_products.php';
	$response = lucas_company_fetch_url($api_url);

	if ($response === '') {
		return lucas_company_empty_data();
	}

	$decoded = json_decode($response, true);

	if (!is_array($decoded) || !isset($decoded['products']) || !is_array($decoded['products'])) {
		return lucas_company_empty_data();
	}

	return [
		'company_name' => trim((string) ($decoded['company_name'] ?? 'Pass & Play')),
		'products' => array_values(array_filter(array_map('lucas_company_normalize_product', $decoded['products']))),
	];
}

function lucas_company_fetch_url($url) {
	if (function_exists('curl_init')) {
		$ch = curl_init($url);

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_USERAGENT => 'LucasCompanyCatalog/1.0',
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
			'header' => "Accept: application/json\r\nUser-Agent: LucasCompanyCatalog/1.0\r\n",
			'ignore_errors' => true,
			'timeout' => 10,
		],
	]);
	$body = @file_get_contents($url, false, $context);

	return is_string($body) ? $body : '';
}

function lucas_company_normalize_product($product) {
	if (!is_array($product)) {
		return null;
	}

	$title = trim((string) ($product['title'] ?? ''));
	$product_link = trim((string) ($product['product_link'] ?? ''));

	if ($title === '' || $product_link === '') {
		return null;
	}

	return [
		'title' => $title,
		'description' => trim((string) ($product['description'] ?? '')),
		'price' => is_numeric($product['price'] ?? null) ? (float) $product['price'] : null,
		'image_link' => trim((string) ($product['image_link'] ?? '')),
		'product_link' => $product_link,
	];
}

function lucas_company_empty_data() {
	return [
		'company_name' => 'Pass & Play',
		'products' => [],
	];
}
