<?php
require_once __DIR__ . '/../includes/helpers.php';
if (isset($_SESSION['user_id'])) {
	redirect(home_for_role($_SESSION['role']));
}
require_once __DIR__ . '/../includes/public_data.php';
require_once __DIR__ . '/../model/Category.php';
require_once __DIR__ . '/../model/Package.php';
require_once __DIR__ . '/../model/Portfolio.php';
require_once __DIR__ . '/../model/Review.php';
require_once __DIR__ . '/../model/User.php';

$stats = public_stats();
$category = new Category();
$categories = $category->getAllWithCounts();
$package = new Package();
$packages = array_slice($package->getAllPackages(), 0, 6);
$portfolio = new Portfolio();
$works = array();
foreach ($portfolio->getAllItems() as $w) { if ($w['image'] != '' && is_file(__DIR__ . '/../images/' . $w['image'])) $works[] = $w; }
$works = array_slice($works, 0, 6);
$review = new Review();
$reviews = array_slice(array_values(array_filter($review->getAllReviews(), function ($r) { return trim($r['comment']) != ''; })), 0, 3);
$user = new User();
$team = array_slice($user->getPhotographersWithStats(), 0, 4);
$cats = array();
foreach ($categories as $c) { $cats[$c['id']] = $c['name']; }
$catIcons = array('camera', 'users', 'calendar-check', 'image', 'star', 'layers');

$pageTitle = 'Professional photography, booked in minutes';
$bodyClass = 'pub';
require_once __DIR__ . '/../includes/auth_header.php';
?>
<style>.auth-theme{display:none}</style>

<header class="pub-nav">
	<div class="container">
		<a class="pub-brand" href="home.php"><span class="brand-mark"><?php echo icon('camera') ?></span><span>Photo<b>Studio</b></span></a>
		<nav class="pub-links">
			<a href="#how">How it works</a><a href="#services">Services</a><a href="#packages">Packages</a><a href="#team">Photographers</a><a href="#reviews">Reviews</a>
		</nav>
		<div class="pub-cta">
			<button class="icon-btn" id="themeBtn" type="button" aria-label="Toggle dark mode"><span class="show-light"><?php echo icon('moon') ?></span><span class="show-dark"><?php echo icon('sun') ?></span></button>
			<a class="btn btn-ghost btn-sm" href="login.php">Login</a>
			<a class="btn btn-primary btn-sm" href="register.php">Get started</a>
		</div>
	</div>
</header>

<section class="hero-pub">
	<div class="container">
		<div class="hero-grid">
			<div>
				<span class="eyebrow"><?php echo icon('zap') ?>Photography booking made simple</span>
				<h1>Capture every moment. <em>Book your shoot in minutes.</em></h1>
				<p class="lead">Choose a package, pick a date and we match you with a trusted photographer. Track your booking live, from approval to delivery.</p>
				<div class="actions">
					<a class="btn btn-primary btn-lg" href="register.php">Book a shoot <?php echo icon('arrow') ?></a>
					<a class="btn btn-ghost btn-lg" href="#packages">View packages</a>
				</div>
				<div class="trust">
					<div class="avatars"><?php foreach (array_slice($team, 0, 4) as $t) { echo avatar($t['name'], $t['profile_pic']); } ?></div>
					<span><?php if ($stats['reviews'] > 0) { ?><b><?php echo e(number_format($stats['rating'], 1)) ?> / 5</b> from <?php echo e($stats['reviews']) ?> verified review<?php echo $stats['reviews'] > 1 ? 's' : '' ?><?php } else { ?>Trusted, verified photographers<?php } ?></span>
				</div>
			</div>
			<div class="hero-visual"><?php include __DIR__ . '/../includes/mock.php'; ?></div>
		</div>
		<div class="stat-strip">
			<div><b><?php echo e($stats['bookings']) ?>+</b><small>Bookings received</small></div>
			<div><b><?php echo e($stats['photographers']) ?></b><small>Professional photographers</small></div>
			<div><b><?php echo e($stats['customers']) ?>+</b><small>Happy customers</small></div>
			<div><b><?php echo $stats['rating'] > 0 ? e(number_format($stats['rating'], 1)) : '5.0' ?> ★</b><small>Average rating</small></div>
		</div>
	</div>
</section>

<section class="sec sec-alt" id="how">
	<div class="container">
		<div class="sec-head reveal"><span class="eyebrow">How it works</span><h2>From idea to photos in four steps</h2><p>No phone calls and no back and forth. Everything happens in one place.</p></div>
		<div class="steps-grid">
			<?php foreach (array(array('Choose a package', 'Compare prices and inclusions for every kind of shoot.'), array('Pick date and time', 'Select a slot and see which photographers are free.'), array('Get a photographer', 'Our manager confirms and assigns the best match for you.'), array('Enjoy and review', 'Track the shoot, download the invoice, rate the experience.')) as $i => $st) { ?>
				<div class="step-card reveal"><div class="num-badge"><?php echo $i + 1 ?></div><h3><?php echo e($st[0]) ?></h3><p><?php echo e($st[1]) ?></p></div>
			<?php } ?>
		</div>
	</div>
</section>

<section class="sec" id="services">
	<div class="container">
		<div class="sec-head reveal"><span class="eyebrow">Services</span><h2>Photography for every occasion</h2><p>Specialist teams for each type of shoot, so you always get the right eye behind the camera.</p></div>
		<div class="svc-grid">
			<?php $i = 0; foreach ($categories as $c) { ?>
				<div class="svc-card reveal">
					<div class="stat-icon"><?php echo icon($catIcons[$i % count($catIcons)]) ?></div>
					<h3><?php echo e($c['name']) ?></h3>
					<p><?php echo e($c['description']) ?></p>
					<a href="#packages">See <?php echo e($c['package_count']) ?> package<?php echo $c['package_count'] == 1 ? '' : 's' ?> <?php echo icon('arrow') ?></a>
				</div>
			<?php $i++; } ?>
		</div>
	</div>
</section>

<section class="sec sec-alt" id="packages">
	<div class="container">
		<div class="sec-head reveal"><span class="eyebrow">Pricing</span><h2>Simple, transparent packages</h2><p>Pick what fits your day. No hidden charges.</p></div>
		<div class="pub-section-tabs"><div class="tabs">
			<button type="button" class="tab active" data-pkg-cat="all">All</button>
			<?php foreach ($categories as $c) { ?><button type="button" class="tab" data-pkg-cat="<?php echo e($c['id']) ?>"><?php echo e($c['name']) ?></button><?php } ?>
		</div></div>
		<div class="grid grid-auto">
			<?php foreach ($packages as $p) { ?>
				<div class="reveal"><div class="card pkg-card" data-cat="<?php echo e($p['category_id']) ?>">
					<span class="pkg-cat"><?php echo e($p['category_name']) ?></span>
					<span class="pkg-name"><?php echo e($p['name']) ?></span>
					<span class="muted"><?php echo icon('clock') ?> <?php echo e($p['duration']) ?></span>
					<div class="pkg-price num"><?php echo e(money($p['price'])) ?></div>
					<ul class="pkg-list"><?php foreach (array_filter(array_map('trim', explode(',', $p['inclusions']))) as $inc) { ?><li><?php echo icon('check') ?><span><?php echo e($inc) ?></span></li><?php } ?></ul>
					<a class="btn btn-primary" href="register.php">Book this package <?php echo icon('arrow') ?></a>
				</div></div>
			<?php } ?>
		</div>
	</div>
</section>

<?php if (count($works)) { ?>
<section class="sec" id="work">
	<div class="container">
		<div class="sec-head reveal"><span class="eyebrow">Our work</span><h2>A glimpse of recent shoots</h2><p>Real samples uploaded by our photographers.</p></div>
		<div class="gallery" id="gallery">
			<?php foreach ($works as $w) { $full = app_url('images/' . $w['image']); ?>
				<div class="g-item reveal" data-cat="<?php echo e($w['category_name']) ?>" data-pg="<?php echo e($w['photographer_id']) ?>" data-full="<?php echo e($full) ?>" data-caption="<?php echo e($w['title'] . ' - ' . $w['photographer_name']) ?>">
					<img src="<?php echo e($full) ?>" alt="<?php echo e($w['title']) ?>" loading="lazy">
					<div class="g-cap"><b><?php echo e($w['title']) ?></b><small><?php echo e($w['photographer_name']) ?> &middot; <?php echo e($w['category_name']) ?></small></div>
				</div>
			<?php } ?>
		</div>
	</div>
</section>
<?php } ?>

<section class="sec sec-alt" id="team">
	<div class="container">
		<div class="sec-head reveal"><span class="eyebrow">The team</span><h2>Meet our photographers</h2><p>Experienced, reviewed by real customers and ready for your date.</p></div>
		<div class="grid grid-auto">
			<?php foreach ($team as $p) { ?>
				<div class="card pg-card reveal">
					<?php echo avatar($p['name'], $p['profile_pic'], 'lg') ?>
					<div class="pg-name"><?php echo e($p['name']) ?></div>
					<span class="tag mt-1"><?php echo e($p['specialization']) ?></span>
					<div class="pg-rating"><?php if ($p['review_count'] > 0) { echo stars($p['avg_rating']) . '<b>' . e($p['avg_rating']) . '</b><span>(' . e($p['review_count']) . ')</span>'; } else { echo '<span>New on the team</span>'; } ?></div>
					<div class="pg-stats"><div><b><?php echo e($p['completed_shoots']) ?></b><small>Shoots</small></div><div><b><?php echo e($p['portfolio_count']) ?></b><small>Works</small></div><div><b><?php echo e($p['review_count']) ?></b><small>Reviews</small></div></div>
					<a class="btn btn-soft btn-sm" href="register.php">Book now</a>
				</div>
			<?php } ?>
		</div>
	</div>
</section>

<?php if (count($reviews)) { ?>
<section class="sec" id="reviews">
	<div class="container">
		<div class="sec-head reveal"><span class="eyebrow">Reviews</span><h2>Loved by our customers</h2><p>Honest feedback from completed shoots.</p></div>
		<div class="review-grid">
			<?php foreach ($reviews as $r) { ?>
				<div class="review-card reveal">
					<div class="flex between items-center"><span class="quote-mark">&ldquo;</span><?php echo stars($r['rating']) ?></div>
					<p><?php echo e($r['comment']) ?></p>
					<div class="who"><?php echo avatar($r['customer_name']) ?><div><b><?php echo e($r['customer_name']) ?></b><small class="muted"><?php echo e($r['package_name']) ?></small></div></div>
				</div>
			<?php } ?>
		</div>
	</div>
</section>
<?php } ?>

<section>
	<div class="container">
		<div class="cta-band reveal">
			<h2>Ready to book your next shoot?</h2>
			<p>Create a free account and send your first booking request in under a minute.</p>
			<div class="actions" style="justify-content:center">
				<a class="btn btn-white btn-lg" href="register.php">Create free account</a>
				<a class="btn btn-ghost btn-lg" href="login.php">Login</a>
			</div>
		</div>
	</div>
</section>

<footer class="pub-foot">
	<div class="container">
		<div class="foot-grid">
			<div><a class="pub-brand" href="home.php" style="padding:0"><span class="brand-mark"><?php echo icon('camera') ?></span><span>Photo<b>Studio</b></span></a>
				<p>Professional photography for weddings, portraits, events and products. Book, track and review in one place.</p></div>
			<div><h4>Explore</h4><a href="#how">How it works</a><a href="#services">Services</a><a href="#packages">Packages</a><a href="#team">Photographers</a></div>
			<div><h4>Account</h4><a href="login.php">Login</a><a href="register.php">Create account</a><a href="reset-password.php">Forgot password</a></div>
			<div class="foot-contact"><h4>Contact</h4>
				<div><?php echo icon('pin') ?>Dhaka, Bangladesh</div><div><?php echo icon('mail') ?>hello@photostudio.com</div><div><?php echo icon('phone') ?>+880 1700 000000</div></div>
		</div>
		<div class="foot-bottom"><span>&copy; <?php echo date('Y') ?> Photo Studio. All rights reserved.</span><span>Photography Studio Management System</span></div>
	</div>
</footer>
<?php require_once __DIR__ . '/../includes/auth_footer.php'; ?>
