<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('customer');
require_once __DIR__ . '/../../model/Review.php';
require_once __DIR__ . '/../../model/Booking.php';

$customerId = $_SESSION['user_id'];
$review = new Review();
$reviewList = $review->getReviewsByCustomer($customerId);
$booking = new Booking();
$bookingList = $booking->getBookingsByCustomer($customerId);
$selectedBookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

$reviewable = array();
foreach ($bookingList as $b) {
	if ($b['status'] == 'completed' && $review->getReviewByBooking($b['id']) == null) $reviewable[] = $b;
}

$pageTitle = 'Reviews & feedback';
$active = 'reviews';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-main">
	<div class="card">
		<div class="card-head"><h2>My reviews</h2></div>
		<div class="card-body flush mt-1">
			<?php foreach ($reviewList as $r) { ?>
				<div class="rv-card">
					<?php echo avatar($r['photographer_name'] ? $r['photographer_name'] : 'Studio', '', '') ?>
					<div class="grow">
						<div class="flex between flex-wrap gap-1"><b><?php echo e($r['package_name']) ?> <small>&middot; <?php echo e(date_short($r['booking_date'])) ?></small></b><?php echo stars($r['rating']) ?></div>
						<small><?php echo e($r['photographer_name'] ? 'Photographer: ' . $r['photographer_name'] : '') ?></small>
						<p class="rv-text"><?php echo e($r['comment']) ?></p>
						<div class="actions">
							<a class="btn btn-ghost btn-sm" href="edit-review.php?id=<?php echo e($r['id']) ?>"><?php echo icon('edit') ?>Edit</a>
							<form class="inline-form" action="<?php echo e(app_url('controller/review-handler.php')) ?>" method="post" data-confirm="Your review will be removed." data-confirm-title="Delete review?" data-confirm-yes="Delete">
								<?php echo csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo e($r['id']) ?>">
								<button class="btn btn-danger-soft btn-sm" type="submit"><?php echo icon('trash') ?>Delete</button>
							</form>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if (count($reviewList) == 0) { echo empty_state('No review yet', 'Complete a shoot, then tell us how it went.', 'star'); } ?>
		</div>
	</div>

	<div class="card">
		<div class="card-head"><h2>Write a review</h2></div>
		<div class="card-body">
			<?php if (count($reviewable) == 0) { echo empty_state('Nothing to review', 'Completed shoots without a review show up here.', 'check'); } else { ?>
			<form action="<?php echo e(app_url('controller/review-handler.php')) ?>" method="post">
				<?php echo csrf_field() ?><input type="hidden" name="action" value="create">
				<div class="field"><label for="booking_id">Completed shoot</label>
					<select id="booking_id" name="booking_id" required>
						<?php foreach ($reviewable as $b) { ?>
							<option value="<?php echo e($b['id']) ?>" <?php echo $b['id'] == $selectedBookingId ? 'selected' : '' ?>>#<?php echo e($b['id'] . ' - ' . $b['package_name'] . ' (' . date_short($b['booking_date']) . ')') ?></option>
						<?php } ?>
					</select></div>
				<div class="field"><div class="label">Your rating</div>
					<div class="star-picker">
						<?php for ($i = 5; $i >= 1; $i--) { ?>
							<input type="radio" name="rating" id="star<?php echo $i ?>" value="<?php echo $i ?>" <?php echo $i == 5 ? 'checked' : '' ?>><label for="star<?php echo $i ?>" title="<?php echo $i ?> stars"><?php echo icon('star') ?></label>
						<?php } ?>
					</div></div>
				<div class="field"><label for="comment">Comment</label><textarea id="comment" name="comment" maxlength="1000" placeholder="What did you like? What could be better?"></textarea></div>
				<button type="submit" class="btn btn-primary">Submit review</button>
			</form>
			<?php } ?>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
