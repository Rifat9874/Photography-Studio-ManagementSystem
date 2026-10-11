<?php
$pageTitle = 'Change password';
$active = 'password';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:520px">
	<div class="card-head"><div><h2>Update your password</h2><p>Use at least 6 characters. A mix of letters, numbers and symbols is stronger.</p></div></div>
	<div class="card-body">
		<form action="<?php echo e(app_url('controller/password-handler.php')) ?>" method="post">
			<?php echo csrf_field() ?>
			<input type="hidden" name="action" value="change">
			<div class="field"><label for="old_password">Current password</label>
				<div class="pw-wrap"><input type="password" id="old_password" name="old_password" required><?php echo pw_toggle() ?></div></div>
			<div class="field"><label for="new_password">New password</label>
				<div class="pw-wrap"><input type="password" id="new_password" name="new_password" data-strength="1" required><?php echo pw_toggle() ?></div></div>
			<div class="field"><label for="confirm_password">Confirm new password</label>
				<div class="pw-wrap"><input type="password" id="confirm_password" name="confirm_password" required><?php echo pw_toggle() ?></div></div>
			<button type="submit" class="btn btn-primary">Change password</button>
		</form>
	</div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
