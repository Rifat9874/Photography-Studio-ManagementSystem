<?php
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	session_unset();
	session_destroy();
	session_start();
	flash_success('You have been logged out.');
}
redirect('view/login.php');
