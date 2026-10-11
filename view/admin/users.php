<?php
require_once __DIR__ . '/../../includes/helpers.php';
require_role('admin');
require_once __DIR__ . '/../../model/User.php';

$user = new User();
$userList = $user->getAllUsers();

$pageTitle = 'User management';
$pageSubtitle = 'Activate, deactivate or remove accounts';
$active = 'users';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="flex between flex-wrap gap-2 items-center mb-2">
	<div class="tabs" data-tabs-for="userTable">
		<button class="tab active" data-status="all">All <span class="count"></span></button>
		<?php foreach (array('customer', 'photographer', 'manager', 'admin') as $r) { ?><button class="tab" data-status="<?php echo $r ?>"><?php echo ucfirst($r) ?>s <span class="count"></span></button><?php } ?>
	</div>
	<a class="btn btn-ghost btn-sm" href="<?php echo e(app_url('controller/export-handler.php?type=users')) ?>"><?php echo icon('download') ?>Export CSV</a>
</div>
<div class="card"><div class="card-body flush"><div class="table-wrap">
	<table id="userTable" data-table data-placeholder="Search name, email, username...">
		<thead><tr><th>User</th><th>Phone</th><th>Role</th><th>Specialization</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
		<tbody>
		<?php foreach ($userList as $u) { ?>
			<tr data-status="<?php echo e($u['role']) ?>">
				<td><div class="cell-user"><?php echo avatar($u['name'], $u['profile_pic']) ?><div><b><?php echo e($u['name']) ?></b><small><?php echo e($u['email']) ?> &middot; @<?php echo e($u['username']) ?></small></div></div></td>
				<td><?php echo e($u['phone']) ?></td>
				<td><span class="tag"><?php echo e(ucfirst($u['role'])) ?></span></td>
				<td><?php echo e($u['specialization'] ? $u['specialization'] : '-') ?></td>
				<td><?php echo status_badge($u['status']) ?></td>
				<td data-sort="<?php echo e($u['created_at']) ?>" class="nowrap"><?php echo e(date_short($u['created_at'])) ?></td>
				<td>
					<?php if ($u['id'] == $_SESSION['user_id']) { echo '<small class="muted">This is you</small>'; } else { ?>
					<div class="actions">
						<form class="inline-form" action="<?php echo e(app_url('controller/user-handler.php')) ?>" method="post">
							<?php echo csrf_field() ?><input type="hidden" name="id" value="<?php echo e($u['id']) ?>"><input type="hidden" name="action" value="<?php echo $u['status'] == 'active' ? 'deactivate' : 'activate' ?>">
							<button class="btn btn-sm <?php echo $u['status'] == 'active' ? 'btn-ghost' : 'btn-success' ?>" type="submit"><?php echo $u['status'] == 'active' ? 'Deactivate' : 'Activate' ?></button>
						</form>
						<?php if ($u['role'] != 'admin') { ?>
						<form class="inline-form" action="<?php echo e(app_url('controller/user-handler.php')) ?>" method="post" data-confirm="<?php echo e($u['name']) ?> and all their data will be removed." data-confirm-title="Remove account?" data-confirm-yes="Remove">
							<?php echo csrf_field() ?><input type="hidden" name="id" value="<?php echo e($u['id']) ?>"><input type="hidden" name="action" value="delete">
							<button class="btn btn-danger-soft btn-sm" type="submit"><?php echo icon('trash') ?></button>
						</form>
						<?php } ?>
					</div>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div></div></div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
