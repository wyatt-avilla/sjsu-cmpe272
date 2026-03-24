<?php
require_once __DIR__ . '/secure/get_db.php';

header('Content-Type: text/plain; charset=UTF-8');

$pdo = get_db();
$stmt = $pdo->query('SELECT first_name, last_name FROM users ORDER BY user_id');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
	echo $row['first_name'] . ' ' . $row['last_name'] . PHP_EOL;
}
