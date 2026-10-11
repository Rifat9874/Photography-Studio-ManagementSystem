<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/User.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('index.php');
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$user = new User();

// logged in user changes his own password
if ($action == 'change') {
	require_login();
	csrf_verify('view/change-password.php');
	$userId = $_SESSION['user_id'];
	$oldPassword = $_POST['old_password'];
	$newPassword = $_POST['new_password'];
	$confirmPassword = $_POST['confirm_password'];

	$row = $user->getUserById($userId);
	if (!$user->verifyPassword($row, $oldPassword)) {
		flash_error("Old password is wrong");
	} else if (strlen($newPassword) < 6) {
		flash_error("New password must be at least 6 characters");
	} else if ($newPassword != $confirmPassword) {
		flash_error("New password and confirm password do not match");
	} else if ($newPassword == $oldPassword) {
		flash_error("New password must be different from the old one");
	} else {
		$user->updatePassword($userId, $newPassword);
		flash_success("Password changed successfully");
	}
	redirect('view/change-password.php');
}

// user forgot the password - username + email must match
if ($action == 'reset') {
	csrf_verify('view/reset-password.php');
	$username = trim($_POST['username']);
	$email = trim($_POST['email']);
	$newPassword = $_POST['new_password'];
	$confirmPassword = $_POST['confirm_password'];

	$row = $user->getUserByUsernameAndEmail($username, $email);
	if ($row == null) {
		flash_error("No account found with this username and email");
	} else if (strlen($newPassword) < 6) {
		flash_error("New password must be at least 6 characters");
	} else if ($newPassword != $confirmPassword) {
		flash_error("New password and confirm password do not match");
	} else {
		$user->updatePassword($row['id'], $newPassword);
		flash_success("Password reset successfully. Please login.");
		redirect('view/login.php');
	}
	redirect('view/reset-password.php');
}
redirect('index.php');
