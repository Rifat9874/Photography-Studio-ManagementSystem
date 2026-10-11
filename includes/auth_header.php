<?php
// Layout for pages shown BEFORE login (login / register / reset password).
require_once __DIR__ . '/helpers.php';
if (!isset($pageTitle)) $pageTitle = 'Photo Studio';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo e($pageTitle) ?> · Photo Studio</title>
	<script>
		(function () {
			var t = localStorage.getItem('ps-theme');
						document.documentElement.setAttribute('data-theme', t === 'dark' ? 'dark' : 'light');
		})();
	</script>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
	<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236366f1' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z'/%3E%3Ccircle cx='12' cy='13' r='3'/%3E%3C/svg%3E">
	<link rel="stylesheet" href="<?php echo e(app_url('css/style.css')) ?>?v=<?php echo (int)@filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body class="<?php echo e(isset($bodyClass) ? $bodyClass : 'auth') ?>">
<button class="icon-btn auth-theme" id="themeBtn" type="button" aria-label="Toggle dark mode">
	<span class="show-light"><?php echo icon('moon') ?></span><span class="show-dark"><?php echo icon('sun') ?></span>
</button>
