<?php
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
	header('Location: login.php');
	exit;
}
