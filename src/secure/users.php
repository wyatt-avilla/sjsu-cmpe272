<?php
require 'check_admin.php';
require_once 'get_db.php';
?>
<html>
<head>
	<title>Users</title>
</head>
<body>
	<h1>Users</h1>
	<p><a href="logout.php">Logout</a></p>
	<ul>
	    <?php
	    $pdo = get_db();

	    $stmt = $pdo->query('SELECT * FROM users');
	    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

		foreach ($rows as $row) {
			echo '<li>' . $row['first_name'] . ' ' . $row['last_name'] . '</li>';
	    }
	    ?>
	</ul>
</html>
