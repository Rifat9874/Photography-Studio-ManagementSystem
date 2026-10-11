<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('customer');
require_once __DIR__ . '/../../model/Category.php';
require_once __DIR__ . '/../../model/Package.php';

$category = new Category();
$categoryList = $category->getAllCategories();
$package = new Package();

// a package can be chosen from the Packages page (?package_id=) or restored after a validation error
$prePkg = isset($_GET['package_id']) ? (int)$_GET['package_id'] : (int)old('package_id', 0);
$preRow = $prePkg > 0 ? $package->getPackageById($prePkg) : null;
$preCat = $preRow ? $preRow['category_id'] : 0;

$pageTitle = 'New booking';
$pageSubtitle = 'Three quick steps and the manager will confirm';
$active = 'new-booking';
require_once __DIR__ . '/../../includes/header.php';
?>
<form action="<?php echo e(app_url('controller/booking-handler.php')) ?>" method="post" id="bookingForm"
	data-pre-pkg="<?php echo e($preRow ? $preRow['id'] : '') ?>" data-pre-cat="<?php echo e($preCat ?: '') ?>" novalidate>
	<?php echo csrf_field() ?>
	<input type="hidden" name="action" value="create">
	<div class="wizard">
		<div class="card" style="margin-bottom:0">
			<div class="card-body">
				<div class="wiz-step">
					<span class="wiz-num">1</span>
					<div class="wiz-body">
						<h3>Choose a package</h3>
						<div class="choice-grid mb-2">
							<?php foreach ($categoryList as $c) { ?>
								<label class="choice"><input type="radio" name="category_id" value="<?php echo e($c['id']) ?>" data-name="<?php echo e($c['name']) ?>">
									<span class="choice-box"><span class="choice-title"><?php echo e($c['name']) ?></span><span class="choice-sub"><?php echo e($c['description']) ?></span></span></label>
							<?php } ?>
						</div>
						<div class="choice-grid" id="pkgChoices"><span class="muted">Pick a category to see its packages.</span></div>
					</div>
				</div>

				<div class="wiz-step">
					<span class="wiz-num">2</span>
					<div class="wiz-body">
						<h3>Date and time</h3>
						<div class="field"><label for="booking_date" class="req">Shoot date</label>
							<input type="date" id="booking_date" name="booking_date" min="<?php echo date('Y-m-d') ?>" value="<?php echo e(old('booking_date')) ?>" required></div>
						<div class="label">Time slot</div>
						<div class="flex flex-wrap gap-1">
							<?php foreach (array('Morning', 'Afternoon', 'Evening') as $slot) { ?>
								<label class="check-pill"><input type="radio" name="booking_time" value="<?php echo $slot ?>" <?php echo old('booking_time', 'Morning') == $slot ? 'checked' : '' ?>><span><?php echo e(time_slot_label($slot)) ?></span></label>
							<?php } ?>
						</div>
						<div class="avail-box" id="availBox">Pick a date to see which photographers are free.</div>
					</div>
				</div>

				<div class="wiz-step">
					<span class="wiz-num">3</span>
					<div class="wiz-body" style="padding-bottom:0">
						<h3>Details</h3>
						<div class="field"><label for="location" class="req">Shoot location</label>
							<div class="input-icon"><?php echo icon('pin') ?><input type="text" id="location" name="location" value="<?php echo e(old('location')) ?>" placeholder="e.g. Banani Community Center, Dhaka" required></div>
							<span class="error" id="location_error"></span></div>
						<div class="field"><label for="note">Note (optional)</label>
							<textarea id="note" name="note" maxlength="500" placeholder="Anything the photographer should know?"><?php echo e(old('note')) ?></textarea></div>
					</div>
				</div>
			</div>
		</div>

		<div class="card summary">
			<div class="card-head"><h2>Booking summary</h2></div>
			<div class="card-body">
				<div class="sum-row"><span>Category</span><b id="sumCat">-</b></div>
				<div class="sum-row"><span>Package</span><b id="sumPkg">-</b></div>
				<div class="sum-row"><span>Duration</span><b id="sumDur">-</b></div>
				<div class="sum-row"><span>Date</span><b id="sumDate">-</b></div>
				<div class="sum-row"><span>Time</span><b id="sumTime">-</b></div>
				<div class="sum-row"><span>Location</span><b id="sumLoc">-</b></div>
				<div class="sum-total"><span>Total</span><b id="sumTotal">Tk 0</b></div>
				<button type="submit" class="btn btn-primary btn-block mt-2">Send booking request <?php echo icon('arrow') ?></button>
				<p class="help center mt-1">No payment now. The manager reviews your request first.</p>
			</div>
		</div>
	</div>
</form>
<?php clear_old(); require_once __DIR__ . '/../../includes/footer.php'; ?>
