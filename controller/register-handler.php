<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/User.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/register.php');
}
csrf_verify('view/register.php');

$name = trim($_POST['name']);
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];
$phone = trim($_POST['phone']);
$role = $_POST['role'];
$specialization = isset($_POST['specialization']) ? $_POST['specialization'] : '';

// server side validation
$errors = array();
if (mb_strlen($name) < 3) {
	$errors[] = "Name must be at least 3 characters";
}
if (!preg_match('/^[A-Za-z0-9_.]{3,30}$/', $username)) {
	$errors[] = "Username must be 3-30 characters (letters, numbers, dot, underscore)";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$errors[] = "Email pattern is invalid";
}
if (strlen($password) < 6) {
	$errors[] = "Password must be at least 6 characters";
}
if ($password != $confirmPassword) {
	$errors[] = "Password and Confirm Password do not match";
}
if (!preg_match('/^[0-9]{11}$/', $phone)) {
	$errors[] = "Phone number must be 11 digits";
}
if ($role != 'customer' && $role != 'photographer') {
	$errors[] = "Invalid role selected";
}
if ($role == 'photographer' && !in_array($specialization, array('Wedding', 'Portrait', 'Event', 'Product'))) {
	$errors[] = "Please choose a specialization";
}
if ($role == 'customer') {
	$specialization = '';
}

$user = new User();
if ($user->isUsernameTaken($username)) {
	$errors[] = "This username is already taken";
}
if ($user->isEmailTaken($email)) {
	$errors[] = "This email is already registered";
}

if (count($errors) > 0) {
	$_SESSION['register_error'] = $errors;
	set_old(array('name' => $name, 'username' => $username, 'email' => $email, 'phone' => $phone, 'role' => $role, 'specialization' => $specialization));
	redirect('view/register.php');
}

$result = $user->insertUser($name, $username, $email, $password, $phone, $role, $specialization);
if (is_int($result)) {
	clear_old();
	flash_success("Account created successfully. Please login.");
	redirect('view/login.php');
}
$_SESSION['register_error'] = array("Unable to create account. Please try again.");
redirect('view/register.php');
