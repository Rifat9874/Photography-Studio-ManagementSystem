<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('photographer');
require_once __DIR__ . '/../../model/Booking.php';
require_once __DIR__ . '/../../model/Portfolio.php';
require_once __DIR__ . '/../../model/Availability.php';
require_once __DIR__ . '/../../model/Review.php';
require_once __DIR__ . '/../../model/Report.php';

$pid = $_SESSION['user_id'];
$booking = new Booking();
$shootList = $booking->getBookingsByPhotographer($pid);
$portfolio = new Portfolio();
$availability = new Availability();
$review = new Review();
$summary = $review->getPhotographerSummary($pid);
$reviewList = $review->getReviewsByPhotographer($pid);
$report = new Report();

$today = date('Y-m-d');
$upcoming = array();
foreach (array_reverse($shootList) as $s) {
	if ($s['booking_date'] >= $today && $s['status'] == 'assigned') $upcoming[] = $s;
}
$freeSlots = 0;
foreach ($availability->getSlotsByPhotographer($pid) as $a) { if ($a['available_date'] >= $today) $freeSlots++; }

$pageTitle = 'Welcome, ' . explode(' ', $_SESSION['name'])[0];
$pageSubtitle = 'Your shoots, ratings and availability at a glance';
$active = 'dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-4">
	<?php
	echo stat_card('Total shoots', count($shootList), 'bookings', '', 'total_shoots');
	echo stat_card('Upcoming', count($upcoming), 'calendar', 's-blue', 'upcoming_shoots');
	echo stat_card('Average rating', $summary['avg'], 'star', 's-amber', 'avg_rating', 'rating');
	echo stat_card('Free slots ahead', $freeSlots, 'calendar-check', 's-green', 'free_slots');
	?>
</div>

<div class="grid grid-main">
	<div class="card">
		<div class="card-head"><div><h2>Next shoots</h2><p>Assigned to you and still ahead</p></div><a class="btn btn-soft btn-sm" href="shoots.php">All shoots</a></div>
		<div class="card-body flush mt-1">
			<?php if (!count($upcoming)) { echo empty_state('No upcoming shoot', 'Add free dates so the manager can assign you.', 'calendar'); } ?>
			<div class="list">
			<?php foreach (array_slice($upcoming, 0, 5) as $s) { ?>
				<div class="list-item">
					<div class="date-box"><b><?php echo e(date('d', strtotime($s['booking_date']))) ?></b><small><?php echo e(date('M', strtotime($s['booking_date']))) ?></small></div>
					<div class="grow"><b><?php echo e($s['package_name']) ?></b><small><?php echo e($s['booking_time']) ?> &middot; <?php echo e($s['customer_name']) ?> &middot; <?php echo e($s['location']) ?></small></div>
					<?php echo status_badge($s['status']) ?>
				</div>
			<?php } ?>
			</div>
		</div>
	</div>
	<div class="card">
		<div class="card-head"><div><h2>Rating breakdown</h2><p><?php echo e($summary['count']) ?> review(s)</p></div></div>
		<div class="card-body">
			<div class="rating-big mb-2"><div class="score"><?php echo $summary['count'] ? e($summary['avg']) : '-' ?></div><div><?php echo stars($summary['avg']) ?><br><small>out of 5</small></div></div>
			<?php for ($i = 5; $i >= 1; $i--) { $pct = $summary['count'] ? round($summary['dist'][$i] / $summary['count'] * 100) : 0; ?>
				<div class="bar-row" style="grid-template-columns:44px 1fr 30px"><span><?php echo $i ?> ★</span><div class="bar-track"><i style="width:<?php echo $pct ?>%"></i></div><b><?php echo e($summary['dist'][$i]) ?></b></div>
			<?php } ?>
		</div>
	</div>
</div>

<div class="grid grid-2">
	<div class="card">
		<div class="card-head"><div><h2>My shoot status</h2></div></div>
		<div class="card-body"><div data-chart="status" class="chart-empty">Loading chart...</div></div>
	</div>
	<div class="card">
		<div class="card-head"><div><h2>What customers said</h2></div></div>
		<div class="card-body flush mt-1">
			<?php foreach (array_slice($reviewList, 0, 3) as $r) { ?>
				<div class="rv-card"><?php echo avatar($r['customer_name']) ?><div class="grow"><div class="flex between"><b><?php echo e($r['customer_name']) ?></b><?php echo stars($r['rating']) ?></div><small><?php echo e($r['package_name']) ?></small><p class="rv-text"><?php echo e($r['comment']) ?></p></div></div>
			<?php } ?>
			<?php if (!count($reviewList)) { echo empty_state('No review yet', '', 'star'); } ?>
		</div>
	</div>
</div>
<p id="ajax_note" class="muted"></p>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
