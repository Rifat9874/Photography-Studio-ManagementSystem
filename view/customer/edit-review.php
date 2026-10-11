<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('customer');
require_once __DIR__ . '/../../model/Review.php';

$review = new Review();
$row = null;
foreach ($review->getReviewsByCustomer($_SESSION['user_id']) as $r) {
	if ($r['id'] == (isset($_GET['id']) ? (int)$_GET['id'] : 0)) $row = $r;
}
if ($row == null) {
	flash_error("Review not found");
	redirect('view/customer/reviews.php');
}
$pageTitle = 'Edit review';
$active = 'reviews';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="card" style="max-width:600px">
	<div class="card-head"><div><h2><?php echo e($row['package_name']) ?></h2><p>Shoot on <?php echo e(date_pretty($row['booking_date'])) ?></p></div></div>
	<div class="card-body">
		<form action="<?php echo e(app_url('controller/review-handler.php')) ?>" method="post">
			<?php echo csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?php echo e($row['id']) ?>">
			<div class="field"><div class="label">Rating</div>
				<div class="star-picker">
					<?php for ($i = 5; $i >= 1; $i--) { ?>
						<input type="radio" name="rating" id="star<?php echo $i ?>" value="<?php echo $i ?>" <?php echo $i == $row['rating'] ? 'checked' : '' ?>><label for="star<?php echo $i ?>"><?php echo icon('star') ?></label>
					<?php } ?>
				</div></div>
			<div class="field"><label for="comment">Comment</label><textarea id="comment" name="comment" maxlength="1000"><?php echo e($row['comment']) ?></textarea></div>
			<div class="form-actions"><button type="submit" class="btn btn-primary">Update review</button><a class="btn btn-ghost" href="reviews.php">Back</a></div>
		</form>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
