<?php
// CSV downloads for the booking manager and the admin
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Booking.php';
require_once __DIR__ . '/../model/Report.php';
require_once __DIR__ . '/../model/User.php';

require_role(array('manager', 'admin'));
$type = isset($_GET['type']) ? $_GET['type'] : 'bookings';

if ($type == 'bookings') {
	$booking = new Booking();
	$rows = array();
	foreach ($booking->getAllBookings() as $b) {
		$rows[] = array($b['id'], $b['customer_name'], $b['customer_phone'], $b['category_name'], $b['package_name'], $b['price'],
			$b['booking_date'], $b['booking_time'], $b['location'], $b['photographer_name'], $b['status']);
	}
	csv_download('bookings-' . date('Y-m-d') . '.csv',
		array('ID', 'Customer', 'Phone', 'Category', 'Package', 'Price (Tk)', 'Date', 'Time', 'Location', 'Photographer', 'Status'), $rows);
}

if ($type == 'revenue' && $_SESSION['role'] == 'admin') {
	$report = new Report();
	$rows = array();
	foreach ($report->byMonth(12) as $m) {
		$rows[] = array($m['label'], $m['bookings'], $m['revenue']);
	}
	csv_download('revenue-by-month-' . date('Y-m-d') . '.csv', array('Month', 'Bookings', 'Confirmed revenue (Tk)'), $rows);
}

if ($type == 'users' && $_SESSION['role'] == 'admin') {
	$user = new User();
	$rows = array();
	foreach ($user->getAllUsers() as $u) {
		$rows[] = array($u['id'], $u['name'], $u['username'], $u['email'], $u['phone'], $u['role'], $u['specialization'], $u['status'], $u['created_at']);
	}
	csv_download('users-' . date('Y-m-d') . '.csv', array('ID', 'Name', 'Username', 'Email', 'Phone', 'Role', 'Specialization', 'Status', 'Joined'), $rows);
}

redirect('index.php');
