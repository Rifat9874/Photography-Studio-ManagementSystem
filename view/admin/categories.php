<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('admin');
require_once __DIR__ . '/../../model/Category.php';

$category = new Category();
$categoryList = $category->getAllWithCounts();
$editCategory = isset($_GET['edit_id']) ? $category->getCategoryById((int)$_GET['edit_id']) : null;

$pageTitle = 'Categories';
$pageSubtitle = 'Photography types offered by the studio';
$active = 'categories';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-main">
	<div class="grid grid-2" style="margin-bottom:0;align-content:start">
		<?php foreach ($categoryList as $c) { ?>
			<div class="card pkg-card">
				<span class="pkg-cat">Category #<?php echo e($c['id']) ?></span>
				<span class="pkg-name"><?php echo e($c['name']) ?></span>
				<p class="muted"><?php echo e($c['description']) ?></p>
				<div class="chip-list mb-2"><span class="tag"><?php echo e($c['package_count']) ?> package(s)</span><span class="tag"><?php echo e($c['booking_count']) ?> booking(s)</span></div>
				<div class="actions">
					<a class="btn btn-ghost btn-sm" href="categories.php?edit_id=<?php echo e($c['id']) ?>"><?php echo icon('edit') ?>Edit</a>
					<form class="inline-form" action="<?php echo e(app_url('controller/category-handler.php')) ?>" method="post" data-confirm="<?php echo $c['booking_count'] > 0 ? 'This category has bookings and cannot be deleted.' : 'The category and its empty packages will be deleted.' ?>" data-confirm-title="Delete category?" data-confirm-yes="Delete">
						<?php echo csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo e($c['id']) ?>">
						<button class="btn btn-danger-soft btn-sm" type="submit"><?php echo icon('trash') ?>Delete</button>
					</form>
				</div>
			</div>
		<?php } ?>
	</div>
	<div class="card">
		<div class="card-head"><h2><?php echo $editCategory ? 'Edit category #' . e($editCategory['id']) : 'Create category' ?></h2></div>
		<div class="card-body">
			<form action="<?php echo e(app_url('controller/category-handler.php')) ?>" method="post">
				<?php echo csrf_field() ?>
				<input type="hidden" name="action" value="<?php echo $editCategory ? 'update' : 'create' ?>">
				<?php if ($editCategory) { ?><input type="hidden" name="id" value="<?php echo e($editCategory['id']) ?>"><?php } ?>
				<div class="field"><label for="name" class="req">Category name</label><input type="text" id="name" name="name" value="<?php echo e($editCategory ? $editCategory['name'] : '') ?>" required></div>
				<div class="field"><label for="description">Description</label><input type="text" id="description" name="description" value="<?php echo e($editCategory ? $editCategory['description'] : '') ?>"></div>
				<div class="form-actions"><button type="submit" class="btn btn-primary"><?php echo $editCategory ? 'Update category' : 'Create category' ?></button><?php if ($editCategory) { ?><a class="btn btn-ghost" href="categories.php">Cancel</a><?php } ?></div>
			</form>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
