<?php
require_once __DIR__ . '/../includes/helpers.php';
if (isset($_SESSION['user_id'])) {
	redirect('index.php');
}
$lastLoginId = isset($_COOKIE['last_login_id']) ? $_COOKIE['last_login_id'] : '';
require_once __DIR__ . '/../includes/public_data.php';
$stats = public_stats();
$pageTitle = 'Login';
require_once __DIR__ . '/../includes/auth_header.php';
?>
<div class="auth-shell">
	<section class="auth-hero">
		<div class="auth-top"><a class="brand" href="home.php"><span class="brand-mark"><?php echo icon('camera') ?></span><span class="brand-text">Photo<b style="-webkit-text-fill-color:#fff;color:#fff;background:none">Studio</b></span></a><a href="home.php">&larr; Back to website</a></div>
		<div>
			<h2>Book, manage and deliver every shoot in one place.</h2>
			<p>One platform for customers, photographers, booking managers and admins.</p>
			</div>
		<div>
			<?php $mockGlass = true; include __DIR__ . '/../includes/mock.php'; ?>
			<div class="auth-stats">
				<div><b><?php echo e($stats['bookings']) ?>+</b><small>Bookings</small></div>
				<div><b><?php echo e($stats['photographers']) ?></b><small>Photographers</small></div>
				<div><b><?php echo $stats['rating'] > 0 ? e(number_format($stats['rating'], 1)) : '5.0' ?> ★</b><small>Avg. rating</small></div>
			</div>
		</div>
	</section>

	<section class="auth-form-side">
		<div class="auth-card">
			<h1>Welcome back</h1>
			<p class="lead">Login to your account to continue.</p>
			<form action="<?php echo e(app_url('controller/login-handler.php')) ?>" method="post">
				<?php echo csrf_field() ?>
				<div class="field">
					<label for="login_id">Username or email</label>
					<div class="input-icon"><?php echo icon('user') ?><input type="text" id="login_id" name="login_id" value="<?php echo e($lastLoginId) ?>" autocomplete="username" required></div>
				</div>
				<div class="field">
					<label for="password">Password</label>
					<div class="pw-wrap"><input type="password" id="password" name="password" autocomplete="current-password" required><?php echo pw_toggle() ?></div>
				</div>
				<button type="submit" class="btn btn-primary btn-block">Login <?php echo icon('arrow') ?></button>
			</form>
			<div class="auth-links">
				<span>New here? <a href="register.php">Create an account</a></span>
				<a href="reset-password.php">Forgot password?</a>
			</div>

			<details class="demo-box" open>
				<summary>Demo accounts (click to fill)</summary>
				<div class="demo-list">
					<button type="button" data-u="admin" data-p="admin123">Admin</button>
					<button type="button" data-u="manager" data-p="manager123">Manager</button>
					<button type="button" data-u="photo1" data-p="photo123">Photographer</button>
					<button type="button" data-u="customer" data-p="customer123">Customer</button>
				</div>
			</details>
		</div>
	</section>
</div>
<?php require_once __DIR__ . '/../includes/auth_footer.php'; ?>
