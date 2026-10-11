<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Review.php';
require_once __DIR__ . '/../model/Booking.php';

require_role('customer');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/customer/reviews.php');
}
csrf_verify('view/customer/reviews.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$review = new Review();
$customerId = $_SESSION['user_id'];

// CREATE
if ($action == 'create') {
	$bookingId = (int)$_POST['booking_id'];
	$rating = (int)$_POST['rating'];
	$comment = trim($_POST['comment']);

	$booking = new Booking();
	$row = $booking->getBookingById($bookingId);

	if ($row == null || $row['customer_id'] != $customerId) {
		flash_error("You cannot review this booking");
	} else if ($row['status'] != 'completed') {
		flash_error("Only completed shoots can be reviewed");
	} else if ($review->getReviewByBooking($bookingId) != null) {
		flash_error("You already reviewed this shoot");
	} else if ($rating < 1 || $rating > 5) {
		flash_error("Please choose a rating from 1 to 5 stars");
	} else if (mb_strlen($comment) > 1000) {
		flash_error("Comment is too long");
	} else {
		$review->insertReview($bookingId, $customerId, $rating, $comment);
		flash_success("Thank you for your review");
	}
}

// UPDATE (only the owner)
if ($action == 'update') {
	$id = (int)$_POST['id'];
	$rating = (int)$_POST['rating'];
	$comment = trim($_POST['comment']);
	$row = $review->getReviewById($id);
	if ($row == null || $row['customer_id'] != $customerId) {
		flash_error("You cannot edit this review");
	} else if ($rating < 1 || $rating > 5) {
		flash_error("Please choose a rating from 1 to 5 stars");
	} else {
		$review->updateReview($id, $rating, $comment);
		flash_success("Review updated");
	}
}

// DELETE (only the owner)
if ($action == 'delete') {
	$row = $review->getReviewById((int)$_POST['id']);
	if ($row != null && $row['customer_id'] == $customerId) {
		$review->deleteReview($row['id']);
		flash_success("Review deleted");
	} else {
		flash_error("You cannot delete this review");
	}
}
redirect('view/customer/reviews.php');
