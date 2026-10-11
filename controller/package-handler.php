<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Package.php';
require_once __DIR__ . '/../model/Category.php';

// package management belongs to the booking manager
require_role('manager');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/manager/packages.php');
}
csrf_verify('view/manager/packages.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$package = new Package();
$category = new Category();

if ($action == 'create' || $action == 'update') {
	$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
	$categoryId = (int)$_POST['category_id'];
	$name = trim($_POST['name']);
	$price = $_POST['price'];
	$duration = trim($_POST['duration']);
	$inclusions = trim($_POST['inclusions']);

	if ($name == '') {
		flash_error("Package name is required");
	} else if ($category->getCategoryById($categoryId) == null) {
		flash_error("Please choose a category");
	} else if (!is_numeric($price) || $price <= 0 || $price > 99999999) {
		flash_error("Price must be a positive number");
	} else if ($action == 'create') {
		$package->insertPackage($categoryId, $name, $price, $duration, $inclusions);
		flash_success("Package created");
	} else if ($package->getPackageById($id) == null) {
		flash_error("Package not found");
	} else {
		$package->updatePackage($id, $categoryId, $name, $price, $duration, $inclusions);
		flash_success("Package updated");
	}
}

// DELETE - a package that already has bookings is protected (deleting it would delete the bookings)
if ($action == 'delete') {
	$id = (int)$_POST['id'];
	$count = $package->countBookings($id);
	if ($count > 0) {
		flash_error("This package has $count booking(s) and cannot be deleted. Edit it instead.");
	} else {
		$package->deletePackage($id);
		flash_success("Package deleted");
	}
}
redirect('view/manager/packages.php');
