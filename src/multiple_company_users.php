<?php
require_once __DIR__ . '/secure/get_db.php';

header('Content-Type: text/plain; charset=UTF-8');

$users = [];

$pdo = get_db();
$stmt = $pdo->query('SELECT first_name, last_name FROM users ORDER BY user_id');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
	$users[] = $row['first_name'] . ' ' . $row['last_name'];
}


function fetch_url($url)
{
	$attempts = [$url];

	if (strpos($url, 'https://') === 0) {
		$attempts[] = 'http://' . substr($url, strlen('https://'));
	}

	foreach ($attempts as $attemptUrl) {
		$response = null;
		$statusCode = 0;

		if (function_exists('curl_init')) {
			$ch = curl_init($attemptUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			$response = curl_exec($ch);
			$statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
		} else {
			$context = stream_context_create([
				'http' => [
					'timeout' => 10,
					'ignore_errors' => true,
				],
			]);
			$response = @file_get_contents($attemptUrl, false, $context);

			if (!empty($http_response_header) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
				$statusCode = (int) $matches[1];
			}
		}

		if (is_string($response) && $response !== '' && $statusCode > 0 && $statusCode < 400) {
			return $response;
		}
	}

	return null;
}

function append_names_from_json($value, array &$users)
{
	if (!is_array($value)) {
		return;
	}

	$hasFirst = isset($value['first_name']) && is_string($value['first_name']);
	$hasLast = isset($value['last_name']) && is_string($value['last_name']);

	if ($hasFirst || $hasLast) {
		$fullName = trim(($value['first_name'] ?? '') . ' ' . ($value['last_name'] ?? ''));
		if ($fullName !== '') {
			$users[] = $fullName;
		}
	}

	if (isset($value['name']) && is_string($value['name']) && trim($value['name']) !== '') {
		$users[] = trim($value['name']);
	}

	foreach ($value as $child) {
		if (is_array($child)) {
			append_names_from_json($child, $users);
		}
	}
}

function append_response_users($response, array &$users)
{
	$trimmed = trim($response);
	if ($trimmed === '') {
		return;
	}

	$decoded = json_decode($trimmed, true);
	if (json_last_error() === JSON_ERROR_NONE) {
		$beforeCount = count($users);
		append_names_from_json($decoded, $users);
		if (count($users) > $beforeCount) {
			return;
		}
	}

	$lines = preg_split('/\R/', $trimmed);
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line !== '') {
			$users[] = $line;
		}
	}
}

$urls = [
	'https://hyunseungsong.com/api/company_users.php',
	'https://cmpe272.robbietambunting.com/amplif-ai/api/users-plain.php',
	'https://uncannydev.com/api/local_users.php',
];

foreach ($urls as $url) {
	$response = fetch_url($url);
	if ($response === null) {
		continue;
	}

	append_response_users($response, $users);
}

echo implode(PHP_EOL, $users) . PHP_EOL;
