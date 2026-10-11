<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Reset password';
require_once __DIR__ . '/../includes/auth_header.php';
?>
<div class="auth-form-side" style="min-height:100vh">
	<div class="auth-card">
		<h1>Reset password</h1>
		<p class="lead">Write your username and the email you registered with.</p>
		<form action="<?php echo e(app_url('controller/password-handler.php')) ?>" method="post">
			<?php echo csrf_field() ?>
			<input type="hidden" name="action" value="reset">
			<div class="field"><label for="username">Username</label><input type="text" id="username" name="username" required></div>
			<div class="field"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
			<div class="field"><label for="new_password">New password</label>
				<div class="pw-wrap"><input type="password" id="new_password" name="new_password" data-strength="1" required><?php echo pw_toggle() ?></div></div>
			<div class="field"><label for="confirm_password">Confirm new password</label>
				<div class="pw-wrap"><input type="password" id="confirm_password" name="confirm_password" required><?php echo pw_toggle() ?></div></div>
			<button type="submit" class="btn btn-primary btn-block">Reset password</button>
		</form>
		<div class="auth-links"><a href="login.php">&larr; Back to login</a></div>
	</div>
</div>
<?php require_once __DIR__ . '/../includes/auth_footer.php'; ?>
