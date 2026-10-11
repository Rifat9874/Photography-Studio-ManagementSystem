<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/User.php';

// user management belongs to the admin
require_role('admin');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/admin/users.php');
}
csrf_verify('view/admin/users.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$id = (int)$_POST['id'];
$user = new User();

// admin should not lock or delete his own account
if ($id == $_SESSION['user_id']) {
	flash_error("You cannot change your own admin account from here");
	redirect('view/admin/users.php');
}

$target = $user->getUserById($id);
if ($target == null) {
	flash_error("User not found");
	redirect('view/admin/users.php');
}

if ($action == 'activate') {
	$user->updateStatus($id, 'active');
	flash_success($target['name'] . " activated");
}
if ($action == 'deactivate') {
	$user->updateStatus($id, 'inactive');
	flash_success($target['name'] . " deactivated");
}
if ($action == 'delete') {
	if ($target['role'] == 'admin') {
		flash_error("Another admin account cannot be removed");
	} else {
		delete_uploaded_image($target['profile_pic']);
		$user->deleteUser($id);
		flash_success("Account removed");
	}
}
redirect('view/admin/users.php');
