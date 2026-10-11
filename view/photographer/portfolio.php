<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('photographer');
require_once __DIR__ . '/../../model/Portfolio.php';
require_once __DIR__ . '/../../model/Category.php';

$pid = $_SESSION['user_id'];
$portfolio = new Portfolio();
$itemList = $portfolio->getItemsByPhotographer($pid);
$category = new Category();
$categoryList = $category->getAllCategories();

$editItem = null;
if (isset($_GET['edit_id'])) {
	$editItem = $portfolio->getItemById((int)$_GET['edit_id']);
	if ($editItem != null && $editItem['photographer_id'] != $pid) $editItem = null;
}

$pageTitle = 'Portfolio';
$pageSubtitle = 'Show customers your best work';
$active = 'portfolio';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-main">
	<div class="card">
		<div class="card-head"><h2>My sample works</h2></div>
		<div class="card-body">
			<div class="gallery" id="gallery">
				<?php foreach ($itemList as $it) { $full = $it['image'] != '' ? app_url('images/' . $it['image']) : ''; ?>
					<div class="g-item" data-cat="<?php echo e($it['category_name']) ?>" data-pg="<?php echo e($pid) ?>" <?php if ($full) { ?>data-full="<?php echo e($full) ?>" data-caption="<?php echo e($it['title']) ?>"<?php } ?>>
						<?php if ($full) { ?><img src="<?php echo e($full) ?>" alt="<?php echo e($it['title']) ?>" loading="lazy"><?php } else { ?><div class="g-ph"><?php echo icon('image') ?></div><?php } ?>
						<div class="g-actions">
							<a class="btn btn-sm" href="portfolio.php?edit_id=<?php echo e($it['id']) ?>" onclick="event.stopPropagation()"><?php echo icon('edit') ?></a>
							<form class="inline-form" action="<?php echo e(app_url('controller/portfolio-handler.php')) ?>" method="post" onclick="event.stopPropagation()" data-confirm="This item and its picture will be deleted." data-confirm-title="Delete this work?" data-confirm-yes="Delete">
								<?php echo csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo e($it['id']) ?>">
								<button class="btn btn-sm" type="submit"><?php echo icon('trash') ?></button>
							</form>
						</div>
						<div class="g-cap"><b><?php echo e($it['title']) ?></b><small><?php echo e($it['category_name']) ?><?php echo $it['description'] != '' ? ' &middot; ' . e($it['description']) : '' ?></small></div>
					</div>
				<?php } ?>
			</div>
			<?php if (!count($itemList)) { echo empty_state('Your portfolio is empty', 'Add your first work using the form.', 'image'); } ?>
		</div>
	</div>

	<div class="card">
		<div class="card-head"><h2><?php echo $editItem ? 'Edit work #' . e($editItem['id']) : 'Add new work' ?></h2></div>
		<div class="card-body">
			<form action="<?php echo e(app_url('controller/portfolio-handler.php')) ?>" method="post" enctype="multipart/form-data">
				<?php echo csrf_field() ?>
				<input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create' ?>">
				<?php if ($editItem) { ?><input type="hidden" name="id" value="<?php echo e($editItem['id']) ?>"><?php } ?>
				<div class="field"><label for="title" class="req">Title</label><input type="text" id="title" name="title" value="<?php echo e($editItem ? $editItem['title'] : '') ?>" required></div>
				<div class="field"><label for="category_id">Category</label>
					<select id="category_id" name="category_id">
						<?php foreach ($categoryList as $c) { ?><option value="<?php echo e($c['id']) ?>" <?php echo ($editItem && $editItem['category_id'] == $c['id']) ? 'selected' : '' ?>><?php echo e($c['name']) ?></option><?php } ?>
					</select></div>
				<div class="field"><label for="description">Description</label><textarea id="description" name="description"><?php echo e($editItem ? $editItem['description'] : '') ?></textarea></div>
				<div class="field"><label for="image">Picture</label>
					<input type="file" id="image" name="image" accept="image/*" data-preview="imgPreview">
					<div id="imgPreview" class="<?php echo ($editItem && $editItem['image']) ? 'img-preview' : '' ?>"><?php if ($editItem && $editItem['image']) { ?><img src="<?php echo e(app_url('images/' . $editItem['image'])) ?>" alt=""><?php } ?></div>
					<div class="help">JPG, PNG, WEBP or GIF, up to 4 MB.</div></div>
				<div class="form-actions"><button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update work' : 'Add work' ?></button><?php if ($editItem) { ?><a class="btn btn-ghost" href="portfolio.php">Cancel</a><?php } ?></div>
			</form>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
