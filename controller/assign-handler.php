<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Booking.php';
require_once __DIR__ . '/../model/User.php';

// only the booking manager can use this controller
require_role('manager');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/manager/bookings.php');
}
csrf_verify('view/manager/bookings.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$id = (int)$_POST['id'];
$booking = new Booking();
$row = $booking->getBookingById($id);

if ($row == null) {
	flash_error("Booking not found");
	redirect('view/manager/bookings.php');
}

// approve or reject a pending request
if ($action == 'approve' || $action == 'reject') {
	if ($row['status'] != 'pending') {
		flash_error("Only pending requests can be approved or rejected");
	} else if ($action == 'approve') {
		$booking->updateStatus($id, 'approved');
		flash_success("Booking #" . $id . " approved. Now assign a photographer.");
	} else {
		$booking->updateStatus($id, 'rejected');
		flash_success("Booking #" . $id . " rejected");
	}
	redirect('view/manager/bookings.php');
}

// assign a photographer to an approved booking
if ($action == 'assign') {
	$photographerId = (int)$_POST['photographer_id'];
	$user = new User();
	$photographer = $user->getUserById($photographerId);

	if ($photographerId <= 0 || $photographer == null || $photographer['role'] != 'photographer' || $photographer['status'] != 'active') {
		flash_error("Please select a valid photographer");
		redirect('view/manager/assign.php?id=' . $id);
	}
	if ($row['status'] != 'approved' && $row['status'] != 'assigned') {
		flash_error("Only an approved booking can get a photographer");
		redirect('view/manager/bookings.php');
	}
	if (!$booking->photographerIsFree($photographerId, $row['booking_date'], $row['booking_time'])) {
		flash_error($photographer['name'] . " did not mark " . $row['booking_time'] . " on " . date_short($row['booking_date']) . " as available");
		redirect('view/manager/assign.php?id=' . $id);
	}
	if ($booking->photographerBusy($photographerId, $row['booking_date'], $row['booking_time'], $id)) {
		flash_error($photographer['name'] . " already has another shoot in this time slot");
		redirect('view/manager/assign.php?id=' . $id);
	}
	$booking->assignPhotographer($id, $photographerId);
	flash_success($photographer['name'] . " assigned to booking #" . $id);
	redirect('view/manager/bookings.php');
}

// manager can also delete a booking record
if ($action == 'delete') {
	$booking->deleteBooking($id);
	flash_success("Booking #" . $id . " deleted");
	redirect('view/manager/bookings.php');
}

redirect('view/manager/bookings.php');
