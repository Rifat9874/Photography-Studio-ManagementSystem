<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Complaint.php';
require_once __DIR__ . '/../model/Booking.php';

require_login();
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('index.php');
}
csrf_verify('index.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$complaint = new Complaint();

// CREATE - customer files a complaint
if ($action == 'create' && $_SESSION['role'] == 'customer') {
	$customerId = $_SESSION['user_id'];
	$bookingId = $_POST['booking_id'] != '' ? (int)$_POST['booking_id'] : null;
	$subject = trim($_POST['subject']);
	$message = trim($_POST['message']);

	if ($subject == '' || $message == '') {
		flash_error("Subject and message are required");
	} else if (mb_strlen($subject) > 150) {
		flash_error("Subject is too long (maximum 150 characters)");
	} else {
		if ($bookingId != null) {
			$booking = new Booking();
			$b = $booking->getBookingById($bookingId);
			if ($b == null || $b['customer_id'] != $customerId) {
				$bookingId = null;
			}
		}
		$complaint->insertComplaint($customerId, $bookingId, $subject, $message);
		flash_success("Complaint submitted. The admin will reply soon.");
	}
	redirect('view/customer/complaints.php');
}

// UPDATE / DELETE - admin only
if ($action == 'update' && $_SESSION['role'] == 'admin') {
	$id = (int)$_POST['id'];
	$status = $_POST['status'];
	$adminReply = trim($_POST['admin_reply']);
	if (!in_array($status, array('open', 'in progress', 'resolved', 'closed'))) {
		flash_error("Invalid status");
	} else if ($complaint->getComplaintById($id) == null) {
		flash_error("Complaint not found");
	} else {
		$complaint->updateComplaint($id, $status, $adminReply);
		flash_success("Complaint #" . $id . " updated");
	}
	redirect('view/admin/complaints.php');
}

if ($action == 'delete' && $_SESSION['role'] == 'admin') {
	$complaint->deleteComplaint((int)$_POST['id']);
	flash_success("Complaint deleted");
	redirect('view/admin/complaints.php');
}

flash_error("This action is not allowed for your account");
redirect('index.php');
