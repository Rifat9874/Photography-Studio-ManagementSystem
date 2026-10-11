<?php
// AJAX + JSON endpoint - called from the registration page while the user types
require_once __DIR__ . '/../model/User.php';

header('Content-Type: application/json');

$username = isset($_GET['username']) ? trim($_GET['username']) : '';

if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) {
	echo json_encode(array('available' => false, 'message' => 'Use 3-30 letters, numbers, dot or underscore'));
	exit();
}

$user = new User();
if ($user->isUsernameTaken($username)) {
	echo json_encode(array('available' => false, 'message' => 'This username is already taken'));
} else {
	echo json_encode(array('available' => true, 'message' => 'Username is available'));
}
