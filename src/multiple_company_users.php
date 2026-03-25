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


function fetch_url(string $url): ?string
{
	$attempts = [$url];

	if (str_starts_with($url, 'https://')) {
		$attempts[] = 'http://' . substr($url, strlen('https://'));
	}

	foreach ($attempts as $attemptUrl) {
		$ch = curl_init($attemptUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$response = curl_exec($ch);
		$statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if (is_string($response) && $response !== '' && $statusCode > 0 && $statusCode < 400) {
			return $response;
		}
	}

	return null;
}

function append_names_from_json(mixed $value, array &$users): void
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

function append_response_users(string $response, array &$users): void
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
