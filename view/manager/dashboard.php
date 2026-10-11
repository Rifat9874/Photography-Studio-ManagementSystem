<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('manager');
require_once __DIR__ . '/../../model/Booking.php';
require_once __DIR__ . '/../../model/Package.php';
require_once __DIR__ . '/../../model/Report.php';

$booking = new Booking();
$bookingList = $booking->getAllBookings();
$package = new Package();
$report = new Report();
$counts = $report->statusCounts();
$totals = $report->revenueTotals();
$pending = array_values(array_filter($bookingList, function ($b) { return $b['status'] == 'pending'; }));
$toAssign = array_values(array_filter($bookingList, function ($b) { return $b['status'] == 'approved'; }));

$pageTitle = 'Manager dashboard';
$pageSubtitle = 'Approve requests and keep every shoot staffed';
$active = 'dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-4">
	<?php
	echo stat_card('All bookings', array_sum($counts), 'bookings', '', 'all_bookings');
	echo stat_card('Waiting for approval', $counts['pending'], 'clock', 's-amber', 'pending_requests');
	echo stat_card('Approved, not assigned', $counts['approved'], 'users', 's-blue', 'approved_bookings');
	echo stat_card('Confirmed revenue', $totals['confirmed'], 'wallet', 's-green', 'confirmed_revenue', 'money');
	?>
</div>

<div class="grid grid-main">
	<div class="card">
		<div class="card-head"><div><h2>New booking requests</h2><p>Approve or reject without leaving the dashboard</p></div><a class="btn btn-soft btn-sm" href="bookings.php">All bookings</a></div>
		<div class="card-body flush mt-1">
			<?php if (!count($pending)) { echo empty_state('All caught up', 'No pending request right now.', 'check'); } ?>
			<div class="list">
			<?php foreach (array_slice($pending, 0, 6) as $b) { ?>
				<div class="list-item">
					<div class="date-box"><b><?php echo e(date('d', strtotime($b['booking_date']))) ?></b><small><?php echo e(date('M', strtotime($b['booking_date']))) ?></small></div>
					<div class="grow"><b><?php echo e($b['package_name']) ?> &middot; <?php echo e(money($b['price'])) ?></b><small><?php echo e($b['customer_name']) ?> &middot; <?php echo e($b['booking_time']) ?> &middot; <?php echo e($b['location']) ?></small></div>
					<div class="actions">
						<?php foreach (array('approve' => array('Approve', 'btn-success', 'check'), 'reject' => array('Reject', 'btn-danger-soft', 'x')) as $act => $cfg) { ?>
						<form class="inline-form" action="<?php echo e(app_url('controller/assign-handler.php')) ?>" method="post">
							<?php echo csrf_field() ?><input type="hidden" name="action" value="<?php echo $act ?>"><input type="hidden" name="id" value="<?php echo e($b['id']) ?>">
							<button class="btn btn-sm <?php echo $cfg[1] ?>" type="submit"><?php echo icon($cfg[2]) . $cfg[0] ?></button>
						</form>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
			</div>
		</div>
	</div>
	<div class="card">
		<div class="card-head"><div><h2>Needs a photographer</h2><p><?php echo count($toAssign) ?> approved booking(s)</p></div></div>
		<div class="card-body flush mt-1">
			<?php if (!count($toAssign)) { echo empty_state('Nothing to assign', '', 'users'); } ?>
			<div class="list">
			<?php foreach (array_slice($toAssign, 0, 5) as $b) { ?>
				<div class="list-item"><div class="grow"><b><?php echo e($b['package_name']) ?></b><small><?php echo e(date_short($b['booking_date'])) ?> &middot; <?php echo e($b['booking_time']) ?></small></div>
					<a class="btn btn-primary btn-sm" href="assign.php?id=<?php echo e($b['id']) ?>">Assign</a></div>
			<?php } ?>
			</div>
		</div>
	</div>
</div>

<div class="grid grid-2">
	<div class="card"><div class="card-head"><div><h2>Revenue by month</h2><p>Confirmed bookings, by shoot date</p></div></div><div class="card-body"><div class="chart" data-chart="revenue"><div class="chart-empty">Loading chart...</div></div></div></div>
	<div class="card"><div class="card-head"><div><h2>Booking status</h2></div></div><div class="card-body"><div data-chart="status" class="chart-empty">Loading chart...</div></div></div>
</div>
<p id="ajax_note" class="muted"></p>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
