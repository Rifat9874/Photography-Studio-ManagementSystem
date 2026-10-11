<?php
// Real numbers from the database for the public pages (login, register, home).
require_once __DIR__ . '/../model/Report.php';
require_once __DIR__ . '/../model/User.php';

function public_stats() {
	$report = new Report();
	$user = new User();
	$counts = $report->statusCounts();
	return array(
		'bookings' => array_sum($counts),
		'completed' => $counts['completed'],
		'photographers' => $user->countByRole('photographer'),
		'customers' => $user->countByRole('customer'),
		'rating' => $report->averageRating(),
		'reviews' => $report->totalReviews()
	);
}
