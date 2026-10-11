<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/User.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/login.php');
}
csrf_verify('view/login.php');

// simple brute-force protection: 5 wrong tries = 60 seconds lock
if (isset($_SESSION['lock_until']) && time() < $_SESSION['lock_until']) {
	$_SESSION['login_error'] = "Too many wrong attempts. Please wait " . ($_SESSION['lock_until'] - time()) . " seconds.";
	redirect('view/login.php');
}

$loginId = isset($_POST['login_id']) ? trim($_POST['login_id']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($loginId == '' || $password == '') {
	$_SESSION['login_error'] = "Username or email and password are required";
	redirect('view/login.php');
}

$user = new User();
$row = $user->loginCheck($loginId, $password);

if ($row == null) {
	$_SESSION['attempts'] = isset($_SESSION['attempts']) ? $_SESSION['attempts'] + 1 : 1;
	if ($_SESSION['attempts'] >= 5) {
		$_SESSION['lock_until'] = time() + 60;
		$_SESSION['attempts'] = 0;
	}
	$_SESSION['login_error'] = "Username/email or password is invalid";
	redirect('view/login.php');
}

if ($row['status'] != 'active') {
	$_SESSION['login_error'] = "Your account is deactivated. Please contact the admin.";
	redirect('view/login.php');
}

// login successful - a fresh session id stops session fixation
session_regenerate_id(true);
unset($_SESSION['attempts'], $_SESSION['lock_until'], $_SESSION['login_error']);
$_SESSION['user_id'] = $row['id'];
$_SESSION['username'] = $row['username'];
$_SESSION['name'] = $row['name'];
$_SESSION['role'] = $row['role'];
$_SESSION['profile_pic'] = $row['profile_pic'];

// cookie remembers only the last typed username / email for the next login page
setcookie('last_login_id', $loginId, array('expires' => time() + 86400 * 7, 'path' => '/', 'samesite' => 'Lax'));

redirect(home_for_role($row['role']));
