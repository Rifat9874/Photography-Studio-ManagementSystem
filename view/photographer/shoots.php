<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('photographer');
require_once __DIR__ . '/../../model/Booking.php';

$booking = new Booking();
$shootList = $booking->getBookingsByPhotographer($_SESSION['user_id']);

$pageTitle = 'Assigned shoots';
$pageSubtitle = 'Mark a shoot completed once the photos are delivered';
$active = 'shoots';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="tabs mb-2" data-tabs-for="shootTable">
	<button class="tab active" data-status="all">All <span class="count"></span></button>
	<button class="tab" data-status="assigned">Upcoming <span class="count"></span></button>
	<button class="tab" data-status="completed">Completed <span class="count"></span></button>
</div>
<div class="card">
	<div class="card-body flush">
		<div class="table-wrap">
		<table id="shootTable" data-table data-placeholder="Search customer, package, location...">
			<thead><tr><th>ID</th><th>Customer</th><th>Package</th><th>Date</th><th>Time</th><th>Location</th><th>Note</th><th>Status</th><th>Action</th></tr></thead>
			<tbody>
			<?php foreach ($shootList as $s) { ?>
				<tr data-status="<?php echo e($s['status']) ?>">
					<td>#<?php echo e($s['id']) ?></td>
					<td><div class="cell-user"><?php echo avatar($s['customer_name']) ?><div><b><?php echo e($s['customer_name']) ?></b><small><?php echo e($s['customer_phone']) ?></small></div></div></td>
					<td><?php echo e($s['package_name']) ?></td>
					<td data-sort="<?php echo e($s['booking_date']) ?>" class="nowrap"><?php echo e(date_short($s['booking_date'])) ?></td>
					<td><?php echo e($s['booking_time']) ?></td>
					<td class="wrap"><?php echo e($s['location']) ?></td>
					<td class="wrap"><?php echo e($s['note']) ?></td>
					<td><?php echo status_badge($s['status']) ?></td>
					<td>
						<?php if ($s['status'] == 'assigned' || $s['status'] == 'completed') {
							$to = $s['status'] == 'assigned' ? 'completed' : 'assigned'; ?>
							<form class="inline-form" action="<?php echo e(app_url('controller/booking-handler.php')) ?>" method="post">
								<?php echo csrf_field() ?><input type="hidden" name="action" value="shoot_status"><input type="hidden" name="id" value="<?php echo e($s['id']) ?>"><input type="hidden" name="status" value="<?php echo $to ?>">
								<button class="btn btn-sm <?php echo $to == 'completed' ? 'btn-success' : 'btn-ghost' ?>" type="submit"><?php echo icon($to == 'completed' ? 'check' : 'x') ?><?php echo $to == 'completed' ? 'Mark completed' : 'Reopen' ?></button>
							</form>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		</div>
	</div>
</div>
<?php if (count($shootList) == 0) { echo '<div class="card card-pad">' . empty_state('No shoot assigned yet', 'Add your availability so the manager can assign you.', 'bookings') . '</div>'; } ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
