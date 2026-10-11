<?php
// AJAX + JSON endpoint - events of one month for the calendar page
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Booking.php';
require_once __DIR__ . '/../model/Availability.php';

header('Content-Type: application/json');
if (empty($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(array('events' => array()));
	exit();
}

$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $month) || !valid_date($month . '-01')) {
	$month = date('Y-m');
}
// a week of padding on both sides so the grey days around the month are filled too
$from = date('Y-m-d', strtotime($month . '-01 -7 day'));
$to = date('Y-m-d', strtotime($month . '-01 +1 month +7 day'));

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$booking = new Booking();
$events = array();

foreach ($booking->getForCalendar($role, $userId, $from, $to) as $b) {
	// customers and photographers see the other side, staff see both
	$who = '';
	if ($role == 'customer') {
		$who = $b['photographer_name'] ? 'Photographer: ' . $b['photographer_name'] : 'Photographer not assigned yet';
	} else if ($role == 'photographer') {
		$who = 'Customer: ' . $b['customer_name'];
	} else {
		$who = 'Customer: ' . $b['customer_name'] . ($b['photographer_name'] ? ' · ' . $b['photographer_name'] : ' · unassigned');
	}
	$events[] = array(
		'date' => $b['booking_date'],
		'type' => 'booking',
		'id' => (int)$b['id'],
		'title' => $b['package_name'],
		'status' => $b['status'],
		'time' => $b['booking_time'],
		'location' => $b['location'],
		'who' => $who
	);
}

// photographers also see their free slots
if ($role == 'photographer') {
	$availability = new Availability();
	foreach ($availability->getSlotsWithBookingState($userId) as $a) {
		if ($a['available_date'] >= $from && $a['available_date'] <= $to && !$a['booked']) {
			$events[] = array('date' => $a['available_date'], 'type' => 'free', 'id' => (int)$a['id'], 'title' => 'Free slot',
				'status' => 'free', 'time' => $a['time_slot'], 'location' => '', 'who' => 'Visible to the manager for assignment');
		}
	}
}

echo json_encode(array('month' => $month, 'events' => $events));
