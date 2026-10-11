<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('customer');
require_once __DIR__ . '/../../model/Booking.php';
require_once __DIR__ . '/../../model/Complaint.php';
require_once __DIR__ . '/../../model/Report.php';

$booking = new Booking();
$bookingList = $booking->getBookingsByCustomer($_SESSION['user_id']);
$complaint = new Complaint();
$report = new Report();
$counts = $report->statusCountsFor('customer_id', $_SESSION['user_id']);
$spent = $report->customerSpend($_SESSION['user_id']);

// the nearest upcoming booking that is still alive
$next = null;
$today = date('Y-m-d');
foreach ($bookingList as $b) {
	if ($b['booking_date'] >= $today && in_array($b['status'], array('pending', 'approved', 'assigned'))) {
		if ($next == null || $b['booking_date'] < $next['booking_date']) $next = $b;
	}
}

$pageTitle = 'Welcome, ' . explode(' ', $_SESSION['name'])[0];
$pageSubtitle = 'Here is what is happening with your shoots';
$active = 'dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="card hero-card">
	<div class="card-body">
		<?php if ($next) { ?>
			<small style="color:rgba(255,255,255,.8);font-weight:700;letter-spacing:.08em;text-transform:uppercase">Your next shoot</small>
			<h2 class="mt-1"><?php echo e($next['package_name']) ?></h2>
			<div class="hero-meta">
				<span><?php echo icon('calendar') ?><?php echo e(date_pretty($next['booking_date'])) ?></span>
				<span><?php echo icon('clock') ?><?php echo e($next['booking_time']) ?></span>
				<span><?php echo icon('pin') ?><?php echo e($next['location']) ?></span>
				<span><?php echo icon('user') ?><?php echo e($next['photographer_name'] ? $next['photographer_name'] : 'Photographer not assigned yet') ?></span>
			</div>
			<a class="btn btn-ghost btn-sm" href="my-bookings.php">View my bookings</a>
		<?php } else { ?>
			<h2>Ready for your next shoot?</h2>
			<p>Browse our packages and send a booking request in a minute.</p>
			<a class="btn btn-white btn-sm" href="new-booking.php"><?php echo icon('calendar-plus') ?>Book a shoot</a>
			<a class="btn btn-ghost btn-sm" href="packages.php">See packages</a>
		<?php } ?>
	</div>
</div>

<div class="grid grid-4">
	<?php
	echo stat_card('Total bookings', count($bookingList), 'bookings', '', 'total_bookings');
	echo stat_card('Pending', $counts['pending'], 'clock', 's-amber', 'pending_bookings');
	echo stat_card('Completed', $counts['completed'], 'check', 's-green', 'completed_bookings');
	echo stat_card('Total spent', $spent, 'wallet', 's-blue', 'total_spent', 'money');
	?>
</div>

<div class="grid grid-main">
	<div class="card">
		<div class="card-head"><div><h2>Latest bookings</h2><p>Your five most recent requests</p></div><a class="btn btn-soft btn-sm" href="my-bookings.php">View all</a></div>
		<div class="card-body flush mt-1">
			<?php if (count($bookingList) == 0) { echo empty_state('No booking yet', 'Your requests will appear here.', 'calendar'); } else { ?>
			<div class="list">
				<?php foreach (array_slice($bookingList, 0, 5) as $b) { ?>
					<div class="list-item">
						<div class="date-box"><b><?php echo e(date('d', strtotime($b['booking_date']))) ?></b><small><?php echo e(date('M', strtotime($b['booking_date']))) ?></small></div>
						<div class="grow"><b><?php echo e($b['package_name']) ?></b><small><?php echo e($b['booking_time']) ?> &middot; <?php echo e($b['photographer_name'] ? $b['photographer_name'] : 'Not assigned yet') ?></small></div>
						<?php echo status_badge($b['status']) ?>
					</div>
				<?php } ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<div class="card">
		<div class="card-head"><div><h2>Booking status</h2><p>All your requests</p></div></div>
		<div class="card-body"><div data-chart="status" class="chart-empty">Loading chart...</div></div>
	</div>
</div>
<p id="ajax_note" class="small muted"></p>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
