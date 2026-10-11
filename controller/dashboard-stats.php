<?php
// AJAX + JSON endpoint - numbers and chart data for the dashboard of the logged-in role
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Booking.php';
require_once __DIR__ . '/../model/Complaint.php';
require_once __DIR__ . '/../model/Package.php';
require_once __DIR__ . '/../model/Category.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Portfolio.php';
require_once __DIR__ . '/../model/Availability.php';
require_once __DIR__ . '/../model/Report.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(array('success' => false, 'message' => 'Please login first'));
	exit();
}

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];
$report = new Report();
$stats = array();
$statusCounts = array();
$months = null;

if ($role == 'customer') {
	$booking = new Booking();
	$list = $booking->getBookingsByCustomer($userId);
	$complaint = new Complaint();
	$statusCounts = $report->statusCountsFor('customer_id', $userId);
	$stats = array(
		'total_bookings' => count($list),
		'pending_bookings' => $statusCounts['pending'],
		'completed_bookings' => $statusCounts['completed'],
		'my_complaints' => count($complaint->getComplaintsByCustomer($userId)),
		'total_spent' => $report->customerSpend($userId)
	);
} else if ($role == 'photographer') {
	$booking = new Booking();
	$shoots = $booking->getBookingsByPhotographer($userId);
	$portfolio = new Portfolio();
	$availability = new Availability();
	$upcoming = 0;
	foreach ($shoots as $s) {
		if ($s['booking_date'] >= date('Y-m-d') && $s['status'] == 'assigned') $upcoming++;
	}
	$freeSlots = 0;
	foreach ($availability->getSlotsByPhotographer($userId) as $a) {
		if ($a['available_date'] >= date('Y-m-d')) $freeSlots++;
	}
	$statusCounts = $report->statusCountsFor('photographer_id', $userId);
	$user = new User();
	$mine = null;
	foreach ($user->getPhotographersWithStats() as $p) {
		if ($p['id'] == $userId) $mine = $p;
	}
	$stats = array(
		'total_shoots' => count($shoots),
		'upcoming_shoots' => $upcoming,
		'portfolio_items' => count($portfolio->getItemsByPhotographer($userId)),
		'free_slots' => $freeSlots,
		'avg_rating' => $mine ? (float)$mine['avg_rating'] : 0,
		'earnings' => $report->photographerEarnings($userId)
	);
} else if ($role == 'manager') {
	$booking = new Booking();
	$package = new Package();
	$statusCounts = $report->statusCounts();
	$months = $report->byMonth(6);
	$totals = $report->revenueTotals();
	$stats = array(
		'all_bookings' => array_sum($statusCounts),
		'pending_requests' => $statusCounts['pending'],
		'approved_bookings' => $statusCounts['approved'],
		'packages' => count($package->getAllPackages()),
		'confirmed_revenue' => $totals['confirmed']
	);
} else if ($role == 'admin') {
	$user = new User();
	$category = new Category();
	$complaint = new Complaint();
	$statusCounts = $report->statusCounts();
	$months = $report->byMonth(6);
	$totals = $report->revenueTotals();
	$stats = array(
		'total_users' => count($user->getAllUsers()),
		'categories' => count($category->getAllCategories()),
		'bookings' => array_sum($statusCounts),
		'open_complaints' => $complaint->countByStatus('open'),
		'confirmed_revenue' => $totals['confirmed'],
		'collected_revenue' => $totals['collected']
	);
} else {
	echo json_encode(array('success' => false, 'message' => 'Unknown role'));
	exit();
}

$charts = array('status' => array('labels' => array_keys($statusCounts), 'values' => array_values($statusCounts)));
if ($months !== null) {
	$charts['months'] = array(
		'labels' => array_column($months, 'label'),
		'revenue' => array_column($months, 'revenue'),
		'bookings' => array_column($months, 'bookings')
	);
}

echo json_encode(array(
	'success' => true,
	'role' => $role,
	'stats' => $stats,
	'charts' => $charts,
	'message' => 'Live numbers updated just now'
));
