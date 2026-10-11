<?php
require_once __DIR__ . '/../includes/helpers.php';
if (isset($_SESSION['user_id'])) {
	redirect('index.php');
}
require_once __DIR__ . '/../includes/public_data.php';
$stats = public_stats();
$pageTitle = 'Create account';
$errors = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : array();
unset($_SESSION['register_error']);
$role = old('role', 'customer');
require_once __DIR__ . '/../includes/auth_header.php';
?>
<div class="auth-shell">
	<section class="auth-hero">
		<div class="auth-top"><a class="brand" href="home.php"><span class="brand-mark"><?php echo icon('camera') ?></span><span class="brand-text">Photo<b style="-webkit-text-fill-color:#fff;color:#fff;background:none">Studio</b></span></a><a href="home.php">&larr; Back to website</a></div>
		<div>
			<h2>Join the studio in under a minute.</h2>
			<p>Customers book shoots and track them live. Photographers share their portfolio and get assigned to jobs.</p>
			</div>
		<div><?php $mockGlass = true; include __DIR__ . '/../includes/mock.php'; ?></div>
	</section>

	<section class="auth-form-side">
		<div class="auth-card wide">
			<h1>Create account</h1>
			<p class="lead">Choose how you want to use Photo Studio.</p>

			<?php if (count($errors) > 0) { ?>
				<div class="err-box"><?php foreach ($errors as $er) { echo e($er) . '<br>'; } ?></div>
			<?php } ?>

			<form action="<?php echo e(app_url('controller/register-handler.php')) ?>" method="post" data-validate="register" novalidate>
				<?php echo csrf_field() ?>
				<div class="choice-grid mb-2" style="grid-template-columns:1fr 1fr">
					<label class="choice"><input type="radio" name="role" value="customer" <?php echo $role != 'photographer' ? 'checked' : '' ?>>
						<span class="choice-box"><span class="choice-title">Customer</span><span class="choice-sub">Book and track shoots</span></span></label>
					<label class="choice"><input type="radio" name="role" value="photographer" <?php echo $role == 'photographer' ? 'checked' : '' ?>>
						<span class="choice-box"><span class="choice-title">Photographer</span><span class="choice-sub">Get assigned to shoots</span></span></label>
				</div>

				<div class="form-grid">
					<div class="field"><label for="name">Full name</label><input type="text" id="name" name="name" value="<?php echo e(old('name')) ?>"><span class="error" id="name_error"></span></div>
					<div class="field"><label for="username">Username</label><input type="text" id="username" name="username" data-check="1" value="<?php echo e(old('username')) ?>" autocomplete="off"><span class="error" id="username_error"></span></div>
					<div class="field"><label for="email">Email</label><input type="email" id="email" name="email" value="<?php echo e(old('email')) ?>"><span class="error" id="email_error"></span></div>
					<div class="field"><label for="phone">Phone (11 digits)</label><input type="text" id="phone" name="phone" inputmode="numeric" maxlength="11" value="<?php echo e(old('phone')) ?>"><span class="error" id="phone_error"></span></div>
				</div>

				<div class="field" id="specialization_box" hidden>
					<label for="specialization">Specialization</label>
					<select id="specialization" name="specialization">
						<?php foreach (array('Wedding', 'Portrait', 'Event', 'Product') as $sp) { ?>
							<option value="<?php echo $sp ?>" <?php echo old('specialization') == $sp ? 'selected' : '' ?>><?php echo $sp ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="form-grid">
					<div class="field"><label for="password">Password</label>
						<div class="pw-wrap"><input type="password" id="password" name="password" data-strength="1" autocomplete="new-password"><?php echo pw_toggle() ?></div>
						<span class="error" id="password_error"></span></div>
					<div class="field"><label for="confirm_password">Confirm password</label>
						<div class="pw-wrap"><input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password"><?php echo pw_toggle() ?></div>
						<span class="error" id="confirm_password_error"></span></div>
				</div>
				<button type="submit" class="btn btn-primary btn-block">Create account <?php echo icon('arrow') ?></button>
			</form>
			<?php clear_old(); ?>
			<div class="auth-links"><span>Already have an account? <a href="login.php">Login</a></span></div>
		</div>
	</section>
</div>
<?php require_once __DIR__ . '/../includes/auth_footer.php'; ?>
