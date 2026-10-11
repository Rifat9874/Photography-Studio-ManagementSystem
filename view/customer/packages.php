<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('customer');
require_once __DIR__ . '/../../model/Category.php';
require_once __DIR__ . '/../../model/Package.php';

$category = new Category();
$categoryList = $category->getAllCategories();
$package = new Package();
$packageList = $package->getAllPackages();

// the most booked package gets a "Popular" ribbon
$popular = 0; $max = 0;
foreach ($packageList as $p) { if ($p['booking_count'] > $max) { $max = $p['booking_count']; $popular = $p['id']; } }

$pageTitle = 'Packages';
$pageSubtitle = 'Compare prices and inclusions, then book in one click';
$active = 'packages';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="tabs mb-2">
	<button type="button" class="tab active" data-pkg-cat="all">All packages</button>
	<?php foreach ($categoryList as $c) { ?><button type="button" class="tab" data-pkg-cat="<?php echo e($c['id']) ?>"><?php echo e($c['name']) ?></button><?php } ?>
</div>

<div class="grid grid-auto">
<?php foreach ($packageList as $p) { ?>
	<div>
		<div class="card pkg-card" data-cat="<?php echo e($p['category_id']) ?>">
			<?php if ($p['id'] == $popular && $max > 0) { ?><span class="pkg-ribbon badge badge-assigned">Popular</span><?php } ?>
			<span class="pkg-cat"><?php echo e($p['category_name']) ?></span>
			<span class="pkg-name"><?php echo e($p['name']) ?></span>
			<span class="muted"><?php echo icon('clock') ?> <?php echo e($p['duration']) ?></span>
			<div class="pkg-price num"><?php echo e(money($p['price'])) ?></div>
			<ul class="pkg-list">
				<?php foreach (array_filter(array_map('trim', explode(',', $p['inclusions']))) as $inc) { ?>
					<li><?php echo icon('check') ?><span><?php echo e($inc) ?></span></li>
				<?php } ?>
			</ul>
			<a class="btn btn-primary" href="new-booking.php?package_id=<?php echo e($p['id']) ?>">Book this package <?php echo icon('arrow') ?></a>
		</div>
	</div>
<?php } ?>
</div>
<?php if (count($packageList) == 0) { echo '<div class="card card-pad">' . empty_state('No package yet', '', 'package') . '</div>'; } ?>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
