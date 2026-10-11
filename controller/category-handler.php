<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Category.php';

// category management belongs to the admin
require_role('admin');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/admin/categories.php');
}
csrf_verify('view/admin/categories.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$category = new Category();

if ($action == 'create') {
	$name = trim($_POST['name']);
	$description = trim($_POST['description']);
	if ($name == '') {
		flash_error("Category name is required");
	} else {
		$category->insertCategory($name, $description);
		flash_success("Category created");
	}
}

if ($action == 'update') {
	$id = (int)$_POST['id'];
	$name = trim($_POST['name']);
	$description = trim($_POST['description']);
	if ($name == '') {
		flash_error("Category name is required");
	} else if ($category->getCategoryById($id) == null) {
		flash_error("Category not found");
	} else {
		$category->updateCategory($id, $name, $description);
		flash_success("Category updated");
	}
}

// DELETE - blocked when bookings use the category (the packages would be removed with their bookings)
if ($action == 'delete') {
	$id = (int)$_POST['id'];
	$bookings = $category->countBookings($id);
	if ($bookings > 0) {
		flash_error("This category has $bookings booking(s) and cannot be deleted.");
	} else {
		$category->deleteCategory($id);
		flash_success("Category deleted (its empty packages were removed too)");
	}
}
redirect('view/admin/categories.php');
