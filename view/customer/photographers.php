<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('customer');
require_once __DIR__ . '/../../model/Portfolio.php';
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../model/Category.php';

$portfolio = new Portfolio();
$itemList = $portfolio->getAllItems();
$user = new User();
$photographers = $user->getPhotographersWithStats();
$category = new Category();
$categories = $category->getAllCategories();

$pageTitle = 'Our photographers';
$pageSubtitle = 'Meet the team and explore their work';
$active = 'photographers';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-auto">
<?php foreach ($photographers as $p) { ?>
	<div class="card pg-card">
		<?php echo avatar($p['name'], $p['profile_pic'], 'lg') ?>
		<div class="pg-name"><?php echo e($p['name']) ?></div>
		<span class="tag mt-1"><?php echo e($p['specialization']) ?></span>
		<div class="pg-rating">
			<?php if ($p['review_count'] > 0) { echo stars($p['avg_rating']) . '<b>' . e($p['avg_rating']) . '</b><span>(' . e($p['review_count']) . ')</span>'; } else { echo '<span>No reviews yet</span>'; } ?>
		</div>
		<div class="pg-stats">
			<div><b><?php echo e($p['completed_shoots']) ?></b><small>Shoots</small></div>
			<div><b><?php echo e($p['portfolio_count']) ?></b><small>Works</small></div>
			<div><b><?php echo e($p['review_count']) ?></b><small>Reviews</small></div>
		</div>
		<div class="actions">
			<a class="btn btn-soft btn-sm" href="#gallery" data-gal-pg="<?php echo e($p['id']) ?>" data-name="<?php echo e($p['name']) ?>"><?php echo icon('image') ?>View work</a>
			<a class="btn btn-ghost btn-sm" href="new-booking.php"><?php echo icon('calendar-plus') ?>Book</a>
		</div>
	</div>
<?php } ?>
</div>
<?php if (count($photographers) == 0) { echo '<div class="card card-pad">' . empty_state('No photographer yet', '', 'users') . '</div>'; } ?>

<div class="card mt-3">
	<div class="card-head">
		<div><h2>Sample work</h2><p id="pgFilterLabel" hidden>Showing work by <b id="pgFilterName"></b> &middot; <a href="#" id="pgFilterClear" onclick="return false">show everyone</a></p></div>
		<div class="tabs">
			<button type="button" class="tab active" data-gal-cat="all">All</button>
			<?php foreach ($categories as $c) { ?><button type="button" class="tab" data-gal-cat="<?php echo e($c['name']) ?>"><?php echo e($c['name']) ?></button><?php } ?>
		</div>
	</div>
	<div class="card-body">
		<div class="gallery" id="gallery">
			<?php foreach ($itemList as $it) {
				$full = $it['image'] != '' ? app_url('images/' . $it['image']) : '';
			?>
				<div class="g-item" data-cat="<?php echo e($it['category_name']) ?>" data-pg="<?php echo e($it['photographer_id']) ?>"
					<?php if ($full) { ?>data-full="<?php echo e($full) ?>" data-caption="<?php echo e($it['title'] . ' - ' . $it['photographer_name']) ?>"<?php } ?>>
					<?php if ($full) { ?><img src="<?php echo e($full) ?>" alt="<?php echo e($it['title']) ?>" loading="lazy"><?php } else { ?><div class="g-ph"><?php echo icon('image') ?></div><?php } ?>
					<div class="g-cap"><b><?php echo e($it['title']) ?></b><small><?php echo e($it['photographer_name']) ?> &middot; <?php echo e($it['category_name']) ?></small></div>
				</div>
			<?php } ?>
		</div>
		<div id="galleryEmpty" hidden><?php echo empty_state('No work to show', 'Try another category.', 'image') ?></div>
		<?php if (count($itemList) == 0) { echo empty_state('No portfolio item yet', '', 'image'); } ?>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
