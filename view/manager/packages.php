<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('manager');
require_once __DIR__ . '/../../model/Package.php';
require_once __DIR__ . '/../../model/Category.php';

$package = new Package();
$packageList = $package->getAllPackages();
$category = new Category();
$categoryList = $category->getAllCategories();
$editPackage = isset($_GET['edit_id']) ? $package->getPackageById((int)$_GET['edit_id']) : null;

$pageTitle = 'Package management';
$pageSubtitle = 'Prices and inclusions shown to customers';
$active = 'packages';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="grid grid-main">
	<div class="card">
		<div class="card-head"><h2>All packages</h2><span class="muted"><?php echo count($packageList) ?> total</span></div>
		<div class="card-body flush mt-1">
			<div class="table-wrap">
			<table data-table data-page-size="8" id="pkgTable" data-placeholder="Search packages...">
				<thead><tr><th>Category</th><th>Name</th><th>Price</th><th>Duration</th><th>Booked</th><th>Actions</th></tr></thead>
				<tbody>
				<?php foreach ($packageList as $p) { ?>
					<tr>
						<td><span class="tag"><?php echo e($p['category_name']) ?></span></td>
						<td><b><?php echo e($p['name']) ?></b><small class="muted" style="display:block;max-width:260px"><?php echo e($p['inclusions']) ?></small></td>
						<td data-sort="<?php echo e($p['price']) ?>" class="nowrap num"><?php echo e(money($p['price'])) ?></td>
						<td><?php echo e($p['duration']) ?></td>
						<td class="num"><?php echo e($p['booking_count']) ?></td>
						<td><div class="actions">
							<a class="btn btn-ghost btn-sm" href="packages.php?edit_id=<?php echo e($p['id']) ?>"><?php echo icon('edit') ?></a>
							<form class="inline-form" action="<?php echo e(app_url('controller/package-handler.php')) ?>" method="post" data-confirm="<?php echo $p['booking_count'] > 0 ? 'This package has bookings and cannot be deleted.' : 'The package will be deleted.' ?>" data-confirm-title="Delete package?" data-confirm-yes="Delete">
								<?php echo csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo e($p['id']) ?>">
								<button class="btn btn-danger-soft btn-sm" type="submit"><?php echo icon('trash') ?></button>
							</form>
						</div></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-head"><h2><?php echo $editPackage ? 'Edit package #' . e($editPackage['id']) : 'Create package' ?></h2></div>
		<div class="card-body">
			<form action="<?php echo e(app_url('controller/package-handler.php')) ?>" method="post">
				<?php echo csrf_field() ?>
				<input type="hidden" name="action" value="<?php echo $editPackage ? 'update' : 'create' ?>">
				<?php if ($editPackage) { ?><input type="hidden" name="id" value="<?php echo e($editPackage['id']) ?>"><?php } ?>
				<div class="field"><label for="category_id">Category</label>
					<select id="category_id" name="category_id"><?php foreach ($categoryList as $c) { ?><option value="<?php echo e($c['id']) ?>" <?php echo ($editPackage && $editPackage['category_id'] == $c['id']) ? 'selected' : '' ?>><?php echo e($c['name']) ?></option><?php } ?></select></div>
				<div class="field"><label for="name" class="req">Package name</label><input type="text" id="name" name="name" value="<?php echo e($editPackage ? $editPackage['name'] : '') ?>" required></div>
				<div class="form-grid">
					<div class="field"><label for="price" class="req">Price (Tk)</label><input type="number" id="price" name="price" step="0.01" min="1" value="<?php echo e($editPackage ? $editPackage['price'] : '') ?>" required></div>
					<div class="field"><label for="duration">Duration</label><input type="text" id="duration" name="duration" placeholder="e.g. 4 hours" value="<?php echo e($editPackage ? $editPackage['duration'] : '') ?>"></div>
				</div>
				<div class="field"><label for="inclusions">Inclusions</label><textarea id="inclusions" name="inclusions" placeholder="Separate items with commas"><?php echo e($editPackage ? $editPackage['inclusions'] : '') ?></textarea><div class="help">Comma separated, shown as a checklist to customers.</div></div>
				<div class="form-actions"><button type="submit" class="btn btn-primary"><?php echo $editPackage ? 'Update package' : 'Create package' ?></button><?php if ($editPackage) { ?><a class="btn btn-ghost" href="packages.php">Cancel</a><?php } ?></div>
			</form>
		</div>
	</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
