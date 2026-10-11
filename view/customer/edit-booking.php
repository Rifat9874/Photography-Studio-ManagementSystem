<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('customer');
require_once __DIR__ . '/../../model/Booking.php';
require_once __DIR__ . '/../../model/Package.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$booking = new Booking();
$row = $booking->getBookingById($id);
if ($row == null || $row['customer_id'] != $_SESSION['user_id'] || $row['status'] != 'pending') {
	flash_error("This booking cannot be edited");
	redirect('view/customer/my-bookings.php');
}
$package = new Package();
$packageList = $package->getAllPackages();

$pageTitle = 'Edit booking #' . $row['id'];
$active = 'my-bookings';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="card" style="max-width:760px">
	<div class="card-head"><div><h2>Edit request</h2><p>Only pending requests can be changed.</p></div></div>
	<div class="card-body">
		<form action="<?php echo e(app_url('controller/booking-handler.php')) ?>" method="post">
			<?php echo csrf_field() ?>
			<input type="hidden" name="action" value="update">
			<input type="hidden" name="id" value="<?php echo e($row['id']) ?>">
			<div class="field"><label for="package_id">Package</label>
				<select id="package_id" name="package_id">
					<?php foreach ($packageList as $p) { ?>
						<option value="<?php echo e($p['id']) ?>" <?php echo $p['id'] == $row['package_id'] ? 'selected' : '' ?>><?php echo e($p['category_name'] . ' - ' . $p['name'] . ' (' . money($p['price']) . ')') ?></option>
					<?php } ?>
				</select></div>
			<div class="form-grid">
				<div class="field"><label for="booking_date">Shoot date</label><input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d') ?>" value="<?php echo e($row['booking_date']) ?>" required></div>
				<div class="field"><label for="booking_time">Time slot</label>
					<select id="booking_time" name="booking_time">
						<?php foreach (array('Morning', 'Afternoon', 'Evening') as $slot) { ?><option value="<?php echo $slot ?>" <?php echo $row['booking_time'] == $slot ? 'selected' : '' ?>><?php echo e(time_slot_label($slot)) ?></option><?php } ?>
					</select></div>
			</div>
			<div class="field"><label for="location">Location</label><input type="text" id="location" name="location" value="<?php echo e($row['location']) ?>" required></div>
			<div class="field"><label for="note">Note</label><textarea id="note" name="note" maxlength="500"><?php echo e($row['note']) ?></textarea></div>
			<div class="form-actions"><button type="submit" class="btn btn-primary">Save changes</button><a class="btn btn-ghost" href="my-bookings.php">Back</a></div>
		</form>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
