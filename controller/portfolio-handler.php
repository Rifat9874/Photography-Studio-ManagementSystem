<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Portfolio.php';
require_once __DIR__ . '/../model/Category.php';

require_role('photographer');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/photographer/portfolio.php');
}
csrf_verify('view/photographer/portfolio.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$portfolio = new Portfolio();
$category = new Category();
$photographerId = $_SESSION['user_id'];

// CREATE - upload a new sample work
if ($action == 'create') {
	$categoryId = (int)$_POST['category_id'];
	$title = trim($_POST['title']);
	$description = trim($_POST['description']);

	if ($title == '') {
		flash_error("Title is required");
	} else if ($category->getCategoryById($categoryId) == null) {
		flash_error("Please choose a category");
	} else {
		$error = '';
		$imageName = save_image_upload('image', $_SESSION['username'], $error);
		if ($error != '') {
			flash_error($error);
		} else {
			$portfolio->insertItem($photographerId, $categoryId, $title, $description, $imageName);
			flash_success("Portfolio item added");
		}
	}
}

// UPDATE
if ($action == 'update') {
	$id = (int)$_POST['id'];
	$categoryId = (int)$_POST['category_id'];
	$title = trim($_POST['title']);
	$description = trim($_POST['description']);
	$row = $portfolio->getItemById($id);

	if ($row == null || $row['photographer_id'] != $photographerId) {
		flash_error("This is not your portfolio item");
	} else if ($title == '' || $category->getCategoryById($categoryId) == null) {
		flash_error("Title and category are required");
	} else {
		$error = '';
		$imageName = save_image_upload('image', $_SESSION['username'], $error);
		if ($error != '') {
			flash_error($error);
		} else {
			$portfolio->updateItem($id, $categoryId, $title, $description);
			if ($imageName != '') {
				delete_uploaded_image($row['image']);
				$portfolio->updateImage($id, $imageName);
			}
			flash_success("Portfolio item updated");
		}
	}
}

// DELETE
if ($action == 'delete') {
	$row = $portfolio->getItemById((int)$_POST['id']);
	if ($row != null && $row['photographer_id'] == $photographerId) {
		delete_uploaded_image($row['image']);
		$portfolio->deleteItem($row['id']);
		flash_success("Portfolio item deleted");
	} else {
		flash_error("This is not your portfolio item");
	}
}
redirect('view/photographer/portfolio.php');
