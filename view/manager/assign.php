<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('manager');
require_once __DIR__ . '/../../model/Booking.php';
require_once __DIR__ . '/../../model/User.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$booking = new Booking();
$b = $booking->getBookingById($id);
if ($b == null) {
	flash_error("Booking not found");
	redirect('view/manager/bookings.php');
}
if ($b['status'] != 'approved' && $b['status'] != 'assigned') {
	flash_error("Approve the booking first, then assign a photographer");
	redirect('view/manager/bookings.php');
}
$user = new User();
// photographers free on the date AND in the customer's time slot, not busy elsewhere
$free = $user->getAvailablePhotographers($b['booking_date'], $b['booking_time'], $id);
// photographers free on the date but in another slot (shown as information)
$others = array();
foreach ($user->getAvailablePhotographers($b['booking_date'], '', $id) as $p) { if ($p['time_slot'] != $b['booking_time']) $others[] = $p; }

$pageTitle = 'Assign photographer';
$pageSubtitle = 'Booking #' . $b['id'] . ' · ' . $b['package_name'];
$active = 'bookings';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-main">
	<div class="card">
		<div class="card-head"><div><h2>Available photographers</h2><p>Free on <?php echo e(date_pretty($b['booking_date'])) ?>, <?php echo e($b['booking_time']) ?></p></div></div>
		<div class="card-body">
			<form action="<?php echo e(app_url('controller/assign-handler.php')) ?>" method="post">
				<?php echo csrf_field() ?><input type="hidden" name="action" value="assign"><input type="hidden" name="id" value="<?php echo e($b['id']) ?>">
				<div class="stack">
				<?php foreach ($free as $p) { $match = $p['specialization'] == $b['category_name']; $disabled = $p['busy'] > 0; ?>
					<label class="pick">
						<input type="radio" name="photographer_id" value="<?php echo e($p['id']) ?>" <?php echo $disabled ? 'disabled' : '' ?> <?php echo $b['photographer_id'] == $p['id'] ? 'checked' : '' ?> required>
						<span class="pick-box">
							<?php echo avatar($p['name'], $p['profile_pic']) ?>
							<span class="pick-grow"><b><?php echo e($p['name']) ?></b>
								<small><?php echo e($p['specialization']) ?> &middot; <?php echo $p['avg_rating'] ? '★ ' . e($p['avg_rating']) . ' (' . e($p['review_count']) . ')' : 'No reviews yet' ?></small></span>
							<?php if ($match) { ?><span class="badge badge-completed">Best match</span><?php } ?>
							<?php if ($disabled) { ?><span class="badge badge-rejected">Busy</span><?php } ?>
							<span class="pick-radio"></span>
						</span>
					</label>
				<?php } ?>
				</div>
				<?php if (!count($free)) { echo empty_state('No photographer is free in this slot', 'Ask photographers to add this date in their availability.', 'calendar'); } else { ?>
					<div class="form-actions mt-2"><button type="submit" class="btn btn-primary"><?php echo icon('check') ?>Assign photographer</button><a class="btn btn-ghost" href="bookings.php">Back</a></div>
				<?php } ?>
			</form>
			<?php if (count($others)) { ?>
				<hr><small class="muted">Free on the same day, but in another slot:</small>
				<div class="chip-list mt-1"><?php foreach ($others as $p) { echo '<span class="tag">' . e($p['name']) . ' · ' . e($p['time_slot']) . '</span>'; } ?></div>
			<?php } ?>
		</div>
	</div>

	<div class="card">
		<div class="card-head"><h2>Booking details</h2><?php echo status_badge($b['status']) ?></div>
		<div class="card-body">
			<div class="sum-row"><span>Customer</span><b><?php echo e($b['customer_name']) ?></b></div>
			<div class="sum-row"><span>Phone</span><b><?php echo e($b['customer_phone']) ?></b></div>
			<div class="sum-row"><span>Category</span><b><?php echo e($b['category_name']) ?></b></div>
			<div class="sum-row"><span>Package</span><b><?php echo e($b['package_name']) ?></b></div>
			<div class="sum-row"><span>Date</span><b><?php echo e(date_pretty($b['booking_date'])) ?></b></div>
			<div class="sum-row"><span>Time</span><b><?php echo e($b['booking_time']) ?></b></div>
			<div class="sum-row"><span>Location</span><b><?php echo e($b['location']) ?></b></div>
			<div class="sum-row"><span>Current photographer</span><b><?php echo e($b['photographer_name'] ? $b['photographer_name'] : 'Not assigned') ?></b></div>
			<?php if ($b['note'] != '') { ?><div class="ticket-msg mt-1"><?php echo e($b['note']) ?></div><?php } ?>
			<div class="sum-total"><span>Price</span><b><?php echo e(money($b['price'])) ?></b></div>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
