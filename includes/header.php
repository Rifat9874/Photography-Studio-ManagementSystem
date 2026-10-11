<?php
// Main app layout (sidebar + top bar). A page sets:
//   $pageTitle  - text in the top bar and the browser tab
//   $active     - key of the sidebar item to highlight
// and then ends with:  require_once __DIR__ . '/../../includes/footer.php';
require_once __DIR__ . '/helpers.php';
require_login();
if (!isset($pageTitle)) $pageTitle = 'Photo Studio';
if (!isset($active)) $active = '';
$navList = nav_items($_SESSION['role']);
$cssVersion = @filemtime(__DIR__ . '/../css/style.css');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo e($pageTitle) ?> · Photo Studio</title>
	<script>
		// apply the saved theme before the page paints (no white flash in dark mode)
		(function () {
			var t = localStorage.getItem('ps-theme');
						document.documentElement.setAttribute('data-theme', t === 'dark' ? 'dark' : 'light');
			if (localStorage.getItem('ps-collapsed') === '1') { document.documentElement.classList.add('sb-collapsed'); }
		})();
	</script>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
	<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z'/%3E%3Ccircle cx='12' cy='13' r='3'/%3E%3C/svg%3E">
	<link rel="stylesheet" href="<?php echo e(app_url('css/style.css')) ?>?v=<?php echo (int)$cssVersion ?>">
</head>
<body class="app">
<div class="shell">
	<aside class="sidebar" id="sidebar">
		<a class="brand" href="<?php echo e(app_url(home_for_role($_SESSION['role']))) ?>">
			<span class="brand-mark"><?php echo icon('camera') ?></span>
			<span class="brand-text">Photo<b>Studio</b></span>
		</a>

		<nav class="nav" aria-label="Main menu">
			<div class="nav-label"><?php echo e(ucfirst($_SESSION['role'])) ?> menu</div>
			<?php foreach ($navList as $item) { ?>
				<a class="nav-link<?php echo $item[0] == $active ? ' active' : '' ?>" href="<?php echo e(app_url($item[2])) ?>" title="<?php echo e($item[1]) ?>">
					<?php echo icon($item[3]) ?><span><?php echo e($item[1]) ?></span>
				</a>
			<?php } ?>
		</nav>

		<div class="sidebar-foot">
			<a class="nav-link<?php echo $active == 'profile' ? ' active' : '' ?>" href="<?php echo e(app_url('view/profile.php')) ?>" title="Profile"><?php echo icon('user') ?><span>Profile</span></a>
			<a class="nav-link<?php echo $active == 'password' ? ' active' : '' ?>" href="<?php echo e(app_url('view/change-password.php')) ?>" title="Password"><?php echo icon('lock') ?><span>Password</span></a>
			<form action="<?php echo e(app_url('controller/logout-handler.php')) ?>" method="post">
				<?php echo csrf_field() ?>
				<button type="submit" class="nav-link nav-logout" title="Logout"><?php echo icon('logout') ?><span>Logout</span></button>
			</form>
		</div>
	</aside>
	<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

	<div class="main">
		<header class="topbar">
			<button class="icon-btn only-mobile" id="menuBtn" aria-label="Open menu"><?php echo icon('menu') ?></button>
			<button class="icon-btn only-desktop" id="collapseBtn" aria-label="Collapse menu"><?php echo icon('menu') ?></button>
			<div class="topbar-title">
				<h1><?php echo e($pageTitle) ?></h1>
				<?php if (isset($pageSubtitle)) { ?><p><?php echo e($pageSubtitle) ?></p><?php } ?>
			</div>
			<div class="topbar-actions">
				<button class="search-trigger" id="paletteBtn" type="button">
					<?php echo icon('search') ?><span>Quick search</span><kbd>Ctrl K</kbd>
				</button>
				<button class="icon-btn" id="themeBtn" type="button" aria-label="Toggle dark mode" title="Dark / light mode">
					<span class="show-light"><?php echo icon('moon') ?></span><span class="show-dark"><?php echo icon('sun') ?></span>
				</button>
				<a class="user-chip" href="<?php echo e(app_url('view/profile.php')) ?>">
					<?php echo avatar($_SESSION['name'], isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : '', 'sm') ?>
					<span class="user-chip-text"><b><?php echo e($_SESSION['name']) ?></b><small><?php echo e(ucfirst($_SESSION['role'])) ?></small></span>
				</a>
			</div>
		</header>
		<main class="content">
