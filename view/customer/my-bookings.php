<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('customer');
require_once __DIR__ . '/../../model/Booking.php';
require_once __DIR__ . '/../../model/Review.php';

$booking = new Booking();
$bookingList = $booking->getBookingsByCustomer($_SESSION['user_id']);
$review = new Review();

$pageTitle = 'My bookings';
$pageSubtitle = 'Track every request from approval to delivery';
$active = 'my-bookings';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="tabs mb-2" data-tabs-for="bookList">
	<button class="tab active" data-status="all">All <span class="count"></span></button>
	<?php foreach (array('pending', 'approved', 'assigned', 'completed', 'cancelled', 'rejected') as $st) { ?>
		<button class="tab" data-status="<?php echo $st ?>"><?php echo ucfirst($st) ?> <span class="count"></span></button>
	<?php } ?>
</div>

<div id="bookList" data-list data-page-size="6" data-placeholder="Search by package, location, photographer...">
<?php foreach ($bookingList as $b) {
	$existing = $b['status'] == 'completed' ? $review->getReviewByBooking($b['id']) : null;
?>
	<div class="card" data-item data-status="<?php echo e($b['status']) ?>">
		<div class="card-body">
			<div class="flex between flex-wrap gap-2 items-center">
				<div class="flex items-center gap-2">
					<div class="date-box"><b><?php echo e(date('d', strtotime($b['booking_date']))) ?></b><small><?php echo e(date('M', strtotime($b['booking_date']))) ?></small></div>
					<div>
						<h3><?php echo e($b['package_name']) ?> <small>#<?php echo e($b['id']) ?></small></h3>
						<small><?php echo e($b['category_name']) ?> &middot; <?php echo e(date_pretty($b['booking_date'])) ?> &middot; <?php echo e($b['booking_time']) ?></small>
					</div>
				</div>
				<div class="flex items-center gap-2"><b class="num"><?php echo e(money($b['price'])) ?></b><?php echo status_badge($b['status']) ?></div>
			</div>
			<div class="mt-2 mb-2"><?php echo booking_steps($b['status']) ?></div>
			<div class="kv">
				<div><small>Location</small><b><?php echo e($b['location']) ?></b></div>
				<div><small>Photographer</small><b><?php echo e($b['photographer_name'] ? $b['photographer_name'] : 'Not assigned yet') ?></b></div>
				<?php if ($b['note'] != '') { ?><div><small>Note</small><b><?php echo e($b['note']) ?></b></div><?php } ?>
			</div>
			<div class="actions mt-2">
				<a class="btn btn-ghost btn-sm" href="invoice.php?id=<?php echo e($b['id']) ?>"><?php echo icon('file') ?>Invoice</a>
				<?php if ($b['status'] == 'pending') { ?>
					<a class="btn btn-ghost btn-sm" href="edit-booking.php?id=<?php echo e($b['id']) ?>"><?php echo icon('edit') ?>Edit</a>
				<?php } ?>
				<?php if (in_array($b['status'], array('pending', 'approved', 'assigned'))) { ?>
					<form class="inline-form" action="<?php echo e(app_url('controller/booking-handler.php')) ?>" method="post" data-confirm="This booking request will be cancelled." data-confirm-title="Cancel booking #<?php echo e($b['id']) ?>?" data-confirm-yes="Yes, cancel it">
						<?php echo csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="id" value="<?php echo e($b['id']) ?>">
						<button class="btn btn-danger-soft btn-sm" type="submit"><?php echo icon('x') ?>Cancel</button>
					</form>
				<?php } ?>
				<?php if ($b['status'] == 'completed') {
					if ($existing == null) { ?><a class="btn btn-primary btn-sm" href="reviews.php?booking_id=<?php echo e($b['id']) ?>"><?php echo icon('star') ?>Write review</a>
					<?php } else { ?><span class="badge badge-completed">Reviewed</span><?php }
				} ?>
				<?php if (in_array($b['status'], array('pending', 'cancelled', 'rejected'))) { ?>
					<form class="inline-form" action="<?php echo e(app_url('controller/booking-handler.php')) ?>" method="post" data-confirm="This booking record will be deleted." data-confirm-title="Delete booking?" data-confirm-yes="Delete">
						<?php echo csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo e($b['id']) ?>">
						<button class="btn btn-danger-soft btn-sm" type="submit"><?php echo icon('trash') ?>Delete</button>
					</form>
				<?php } ?>
			</div>
		</div>
	</div>
<?php } ?>
</div>
<?php if (count($bookingList) == 0) { ?>
	<div class="card card-pad"><?php echo empty_state('No booking yet', 'Choose a package and send your first request.', 'calendar') ?><div class="center"><a class="btn btn-primary" href="new-booking.php">Make a booking</a></div></div>
<?php } ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
