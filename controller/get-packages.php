<?php
// AJAX + JSON endpoint - packages of the selected category
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Package.php';

header('Content-Type: application/json');
if (empty($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(array('packages' => array(), 'message' => 'Please login first'));
	exit();
}

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$package = new Package();
echo json_encode(array('packages' => $package->getPackagesByCategory($categoryId)));
