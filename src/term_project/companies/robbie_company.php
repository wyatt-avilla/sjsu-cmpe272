<?php

function robbie_company_get_data() {
	$api_url = getenv('ROBBIE_COMPANY_API_URL') ?: 'http://cmpe272.robbietambunting.com/amplif-ai/api/products.php';
	
	// Extract base site URL by removing the API path so links point to the correct directory
	$site_url = str_replace('/api/products.php', '', $api_url);
	
	$response = robbie_company_fetch_url($api_url);

	if ($response === '') {
		return robbie_company_empty_data();
	}

	$decoded = json_decode($response, true);

	if (!is_array($decoded) || !isset($decoded['products']) || !is_array($decoded['products'])) {
		return robbie_company_empty_data();
	}

	return [
		'company_name' => trim((string) ($decoded['company_name'] ?? 'Robbie Company')),
		'products' => array_values(array_filter(array_map(function ($product) use ($site_url) {
			return robbie_company_normalize_product($product, $site_url);
		}, $decoded['products']))),
	];
}

function robbie_company_fetch_url($url) {
	if (function_exists('curl_init')) {
		$ch = curl_init($url);

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 3,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_USERAGENT => 'RobbieCompanyCatalog/1.0',
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
			'header' => "Accept: application/json\r\nUser-Agent: RobbieCompanyCatalog/1.0\r\n",
			'ignore_errors' => true,
			'timeout' => 10,
		],
	]);
	$body = @file_get_contents($url, false, $context);

	return is_string($body) ? $body : '';
}

function robbie_company_normalize_product($product, $site_url = 'http://cmpe272.robbietambunting.com/amplif-ai') {
	if (!is_array($product)) {
		return null;
	}

	$title = trim((string) ($product['title'] ?? ''));
	$id = trim((string) ($product['id'] ?? ''));
	
	// Use provided product link, or generate it using the site URL and product ID
	$product_link = trim((string) ($product['product_link'] ?? ''));
	if ($product_link === '') {
		if ($id !== '') {
			$product_link = $site_url . '/index.php?page=product&id=' . urlencode($id);
		} else {
			$product_link = $site_url . '/';
		}
	} else if (!preg_match('/^https?:\/\//i', $product_link)) {
		$product_link = $site_url . '/' . ltrim($product_link, '/');
	}

	if ($title === '') {
		return null;
	}

	$description = '';
	if (isset($product['description'])) {
		if (is_array($product['description'])) {
			$description = implode("\n\n", $product['description']);
		} else {
			$description = trim((string) $product['description']);
		}
	} elseif (isset($product['tagline'])) {
		$description = trim((string) $product['tagline']);
	}

	return [
		'title' => $title,
		'description' => $description,
		'price' => is_numeric($product['price'] ?? null) ? (float) $product['price'] : null,
		'image_link' => trim((string) ($product['image_url'] ?? '')),
		'product_link' => $product_link,
	];
}

function robbie_company_empty_data() {
	return [
		'company_name' => 'Robbie Company',
		'products' => [],
	];
}
