<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/User.php';

require_login();
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/profile.php');
}
csrf_verify('view/profile.php');

$userId = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$user = new User();
$current = $user->getUserById($userId);

// UPDATE profile information
if ($action == 'update') {
	$name = trim($_POST['name']);
	$email = trim($_POST['email']);
	$phone = trim($_POST['phone']);
	$specialization = $current['specialization'];
	if ($current['role'] == 'photographer' && isset($_POST['specialization'])) {
		$specialization = $_POST['specialization'];
	}

	if (mb_strlen($name) < 3) {
		flash_error("Name must be at least 3 characters");
	} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		flash_error("Email pattern is invalid");
	} else if ($user->isEmailTaken($email, $userId)) {
		flash_error("This email is already used by another account");
	} else if ($phone != '' && !preg_match('/^[0-9]{11}$/', $phone)) {
		flash_error("Phone number must be 11 digits");
	} else {
		$user->updateProfile($userId, $name, $email, $phone, $specialization);
		$_SESSION['name'] = $name;
		flash_success("Profile updated successfully");
	}
}

// UPLOAD profile picture (type, size and file name are checked)
if ($action == 'upload') {
	$error = '';
	$fileName = save_image_upload('profile_pic', 'avatar' . $userId, $error);
	if ($fileName != '') {
		delete_uploaded_image($current['profile_pic']);
		$user->updateProfilePic($userId, $fileName);
		flash_success("Profile picture updated");
	} else {
		flash_error($error != '' ? $error : "Please choose a picture");
	}
}

// DELETE own account (the admin account is protected)
if ($action == 'delete') {
	if ($current['role'] == 'admin') {
		flash_error("The admin account cannot be deleted from here");
	} else {
		delete_uploaded_image($current['profile_pic']);
		$user->deleteUser($userId);
		session_unset();
		session_destroy();
		session_start();
		flash_success("Your account was deleted.");
		redirect('view/login.php');
	}
}

redirect('view/profile.php');
