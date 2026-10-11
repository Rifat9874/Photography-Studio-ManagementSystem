<?php
$pageTitle = 'My profile';
$active = 'profile';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../model/User.php';
$user = new User();
$me = $user->getUserById($_SESSION['user_id']);
?>
<div class="grid grid-main">
	<div>
		<div class="card">
			<div class="card-body">
				<div class="profile-head">
					<?php echo avatar($me['name'], $me['profile_pic'], 'xl') ?>
					<div>
						<h2><?php echo e($me['name']) ?></h2>
						<p class="muted mb-1">@<?php echo e($me['username']) ?> &middot; <?php echo status_badge($me['role']) ?></p>
						<small>Member since <?php echo e(date_short($me['created_at'])) ?></small>
					</div>
				</div>
				<form action="<?php echo e(app_url('controller/profile-handler.php')) ?>" method="post" enctype="multipart/form-data" class="mt-2">
					<?php echo csrf_field() ?>
					<input type="hidden" name="action" value="upload">
					<div class="field"><label for="profile_pic">Change profile picture</label>
						<input type="file" id="profile_pic" name="profile_pic" accept="image/*" data-preview="picPreview" required>
						<div id="picPreview"></div><div class="help">JPG, PNG, WEBP or GIF, up to 4 MB.</div></div>
					<button type="submit" class="btn btn-soft btn-sm"><?php echo icon('upload') ?>Upload picture</button>
				</form>
			</div>
		</div>

		<div class="card">
			<div class="card-head"><h2>Account information</h2></div>
			<div class="card-body">
				<form action="<?php echo e(app_url('controller/profile-handler.php')) ?>" method="post">
					<?php echo csrf_field() ?>
					<input type="hidden" name="action" value="update">
					<div class="form-grid">
						<div class="field"><label for="name">Full name</label><input type="text" id="name" name="name" value="<?php echo e($me['name']) ?>" required></div>
						<div class="field"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo e($me['email']) ?>" required></div>
						<div class="field"><label for="phone">Phone</label><input type="text" id="phone" name="phone" value="<?php echo e($me['phone']) ?>" maxlength="11"></div>
						<?php if ($me['role'] == 'photographer') { ?>
						<div class="field"><label for="specialization">Specialization</label>
							<select id="specialization" name="specialization">
								<?php foreach (array('Wedding', 'Portrait', 'Event', 'Product') as $sp) { ?>
									<option value="<?php echo $sp ?>" <?php echo $me['specialization'] == $sp ? 'selected' : '' ?>><?php echo $sp ?></option>
								<?php } ?>
							</select></div>
						<?php } ?>
					</div>
					<button type="submit" class="btn btn-primary">Save changes</button>
				</form>
			</div>
		</div>
	</div>

	<div>
		<div class="card">
			<div class="card-head"><h2>Security</h2></div>
			<div class="card-body">
				<p class="muted">Keep your account safe by using a strong, unique password.</p>
				<a class="btn btn-ghost btn-sm" href="change-password.php"><?php echo icon('lock') ?>Change password</a>
			</div>
		</div>
		<?php if ($me['role'] != 'admin') { ?>
		<div class="card danger-zone">
			<div class="card-head"><h2 style="color:var(--danger)">Delete account</h2></div>
			<div class="card-body">
				<p class="muted">This removes your account and all data connected to it. It cannot be undone.</p>
				<form action="<?php echo e(app_url('controller/profile-handler.php')) ?>" method="post"
					data-confirm="Your account and all its bookings will be deleted permanently." data-confirm-title="Delete your account?" data-confirm-yes="Yes, delete">
					<?php echo csrf_field() ?>
					<input type="hidden" name="action" value="delete">
					<button type="submit" class="btn btn-danger-soft btn-sm"><?php echo icon('trash') ?>Delete my account</button>
				</form>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
