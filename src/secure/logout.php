<?php
require_once __DIR__ . '/auth.php';

$redirect = auth_safe_redirect($_GET['redirect'] ?? 'login.php', 'login.php');

auth_logout();

header('Location: ' . $redirect);
exit;
