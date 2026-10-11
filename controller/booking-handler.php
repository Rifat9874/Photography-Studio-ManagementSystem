<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Booking.php';
require_once __DIR__ . '/../model/Package.php';

require_login();
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('index.php');
}
csrf_verify('index.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$booking = new Booking();
$package = new Package();
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// shared validation for create + update
function validate_booking_input($package, $booking, $customerId, $exceptId, &$data) {
	$data = array(
		'package_id' => isset($_POST['package_id']) ? (int)$_POST['package_id'] : 0,
		'booking_date' => isset($_POST['booking_date']) ? $_POST['booking_date'] : '',
		'booking_time' => isset($_POST['booking_time']) ? $_POST['booking_time'] : '',
		'location' => trim(isset($_POST['location']) ? $_POST['location'] : ''),
		'note' => trim(isset($_POST['note']) ? $_POST['note'] : '')
	);
	if ($data['package_id'] <= 0 || $package->getPackageById($data['package_id']) == null) {
		return "Please select a valid package";
	}
	if (!valid_date($data['booking_date'])) {
		return "Please choose a valid shoot date";
	}
	if ($data['booking_date'] < date('Y-m-d')) {
		return "Booking date cannot be in the past";
	}
	if (!valid_slot($data['booking_time'])) {
		return "Please choose a time slot";
	}
	if (mb_strlen($data['location']) < 5) {
		return "Please write the full shoot location";
	}
	if (mb_strlen($data['note']) > 500) {
		return "Note is too long (maximum 500 characters)";
	}
	if ($booking->customerHasSlot($customerId, $data['booking_date'], $data['booking_time'], $exceptId)) {
		return "You already have a booking on that date and time slot";
	}
	return '';
}

// CREATE - customer sends a new booking request
if ($action == 'create' && $role == 'customer') {
	$error = validate_booking_input($package, $booking, $userId, 0, $data);
	if ($error != '') {
		flash_error($error);
		set_old($data);
		redirect('view/customer/new-booking.php');
	}
	$result = $booking->insertBooking($userId, $data['package_id'], $data['booking_date'], $data['booking_time'], $data['location'], $data['note']);
	if (is_int($result)) {
		clear_old();
		flash_success("Booking request sent. Please wait for manager approval.");
	} else {
		flash_error("Unable to create the booking. Please try again.");
	}
	redirect('view/customer/my-bookings.php');
}

// UPDATE - customer edits a booking that is still pending
if ($action == 'update' && $role == 'customer') {
	$id = (int)$_POST['id'];
	$row = $booking->getBookingById($id);
	if ($row == null || $row['customer_id'] != $userId) {
		flash_error("You cannot edit this booking");
	} else if ($row['status'] != 'pending') {
		flash_error("Only pending bookings can be edited");
	} else {
		$error = validate_booking_input($package, $booking, $userId, $id, $data);
		if ($error != '') {
			flash_error($error);
			redirect('view/customer/edit-booking.php?id=' . $id);
		}
		$booking->updateBooking($id, $data['package_id'], $data['booking_date'], $data['booking_time'], $data['location'], $data['note']);
		flash_success("Booking updated successfully");
	}
	redirect('view/customer/my-bookings.php');
}

// DELETE - customer removes his own booking (only pending / cancelled / rejected)
if ($action == 'delete' && $role == 'customer') {
	$row = $booking->getBookingById((int)$_POST['id']);
	if ($row == null || $row['customer_id'] != $userId) {
		flash_error("You cannot delete this booking");
	} else if (!in_array($row['status'], array('pending', 'cancelled', 'rejected'))) {
		flash_error("Approved or completed bookings cannot be deleted. Cancel it instead.");
	} else {
		$booking->deleteBooking($row['id']);
		flash_success("Booking deleted");
	}
	redirect('view/customer/my-bookings.php');
}

// UPDATE status - customer cancels (before the shoot is completed)
if ($action == 'cancel' && $role == 'customer') {
	$row = $booking->getBookingById((int)$_POST['id']);
	if ($row == null || $row['customer_id'] != $userId) {
		flash_error("You cannot cancel this booking");
	} else if (!in_array($row['status'], array('pending', 'approved', 'assigned'))) {
		flash_error("This booking can no longer be cancelled");
	} else {
		$booking->updateStatus($row['id'], 'cancelled');
		flash_success("Booking #" . $row['id'] . " cancelled");
	}
	redirect('view/customer/my-bookings.php');
}

// UPDATE status - photographer marks the shoot completed (or back to assigned)
if ($action == 'shoot_status' && $role == 'photographer') {
	$row = $booking->getBookingById((int)$_POST['id']);
	$status = isset($_POST['status']) ? $_POST['status'] : '';
	if ($row == null || $row['photographer_id'] != $userId) {
		flash_error("This shoot is not assigned to you");
	} else if (!in_array($status, array('assigned', 'completed'))) {
		flash_error("Invalid status");
	} else if (!in_array($row['status'], array('assigned', 'completed'))) {
		flash_error("This booking is not active");
	} else {
		$booking->updateStatus($row['id'], $status);
		flash_success("Shoot #" . $row['id'] . " marked as " . $status);
	}
	redirect('view/photographer/shoots.php');
}

flash_error("This action is not allowed for your account");
redirect('index.php');
