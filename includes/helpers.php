<?php
// =====================================================================
// Shared helpers: session, security (CSRF / XSS), guards, formatting,
// icons and the sidebar menu. Every page and controller loads this file.
// =====================================================================

if (session_status() === PHP_SESSION_NONE) {
	session_set_cookie_params(array('httponly' => true, 'samesite' => 'Lax'));
	session_start();
}
date_default_timezone_set('Asia/Dhaka');

// ---------- output escaping (stops XSS) ----------
function e($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ---------- URLs (works in XAMPP sub-folder or php -S) ----------
function app_url($path = '') {
	static $root = null;
	if ($root === null) {
		$root = '';
		$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
		$appRoot = realpath(__DIR__ . '/..');
		if ($docRoot && $appRoot) {
			$docRoot = rtrim(str_replace('\\', '/', $docRoot), '/');
			$appRoot = str_replace('\\', '/', $appRoot);
			if (stripos($appRoot, $docRoot) === 0) {
				$root = substr($appRoot, strlen($docRoot));
			}
		}
	}
	return $root . '/' . ltrim($path, '/');
}

function redirect($path) {
	if (preg_match('#^(https?:)?//#', $path)) {
		header('Location: ' . $path);
	} else {
		header('Location: ' . app_url($path));
	}
	exit();
}

// ---------- flash messages (shown as toasts) ----------
function flash_success($msg) { $_SESSION['message'] = $msg; }
function flash_error($msg)   { $_SESSION['error'] = $msg; }

// ---------- CSRF protection ----------
function csrf_token() {
	if (empty($_SESSION['csrf'])) {
		$_SESSION['csrf'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf'];
}

function csrf_field() {
	return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// call at the top of every controller that handles a POST
function csrf_verify($fallback = 'index.php') {
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$sent = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
		if (!hash_equals(csrf_token(), $sent)) {
			flash_error('Your session token expired. Please try again.');
			redirect($fallback);
		}
	}
}

// ---------- guards ----------
function require_login() {
	if (empty($_SESSION['user_id'])) {
		redirect('view/login.php');
	}
	// keep the session in sync with the database (deactivated / deleted users are kicked out)
	require_once __DIR__ . '/../model/User.php';
	$u = new User();
	$row = $u->getUserById($_SESSION['user_id']);
	if ($row == null || $row['status'] != 'active') {
		session_unset();
		session_destroy();
		session_start();
		flash_error('Your account is not available. Please contact the admin.');
		redirect('view/login.php');
	}
	$_SESSION['name'] = $row['name'];
	$_SESSION['role'] = $row['role'];
	$_SESSION['profile_pic'] = $row['profile_pic'];
}

function require_role($roles) {
	require_login();
	if (!in_array($_SESSION['role'], (array)$roles, true)) {
		redirect('index.php');
	}
}

function home_for_role($role) {
	if ($role == 'admin') return 'view/admin/dashboard.php';
	if ($role == 'manager') return 'view/manager/dashboard.php';
	if ($role == 'photographer') return 'view/photographer/dashboard.php';
	return 'view/customer/dashboard.php';
}

// ---------- formatting ----------
function money($n) {
	return 'Tk ' . number_format((float)$n, 0);
}

function date_pretty($d) {
	if (!$d) return '-';
	return date('D, d M Y', strtotime($d));
}

function date_short($d) {
	if (!$d) return '-';
	return date('d M Y', strtotime($d));
}

function initials($name) {
	$parts = preg_split('/\s+/', trim((string)$name));
	$s = '';
	for ($i = 0; $i < count($parts) && $i < 2; $i++) {
		$s .= mb_strtoupper(mb_substr($parts[$i], 0, 1));
	}
	return $s == '' ? '?' : $s;
}

function avatar($name, $pic = '', $size = '') {
	$cls = 'avatar' . ($size ? ' avatar-' . $size : '');
	if ($pic != '' && is_file(__DIR__ . '/../images/' . $pic)) {
		return '<span class="' . $cls . '"><img src="' . e(app_url('images/' . $pic)) . '" alt=""></span>';
	}
	$hue = (crc32((string)$name) % 360);
	return '<span class="' . $cls . '" style="--h:' . $hue . '">' . e(initials($name)) . '</span>';
}

function status_badge($status) {
	$slug = strtolower(str_replace(' ', '-', (string)$status));
	return '<span class="badge badge-' . e($slug) . '">' . e(ucfirst($status)) . '</span>';
}

function stars($rating, $max = 5) {
	$out = '<span class="stars" aria-label="' . e($rating) . ' out of ' . $max . '">';
	for ($i = 1; $i <= $max; $i++) {
		$out .= '<span class="star' . ($i <= round($rating) ? ' on' : '') . '">' . icon('star') . '</span>';
	}
	return $out . '</span>';
}

function time_slot_label($slot) {
	$map = array('Morning' => '08:00 - 12:00', 'Afternoon' => '12:00 - 17:00', 'Evening' => '17:00 - 21:00');
	return isset($map[$slot]) ? $slot . ' (' . $map[$slot] . ')' : $slot;
}

// ---------- safe image upload (type, size and name are checked) ----------
function save_image_upload($field, $prefix, &$error) {
	$error = '';
	if (!isset($_FILES[$field]) || $_FILES[$field]['error'] == UPLOAD_ERR_NO_FILE) {
		return '';
	}
	$file = $_FILES[$field];
	if ($file['error'] != UPLOAD_ERR_OK) {
		$error = 'Upload failed. Please try again.';
		return '';
	}
	if ($file['size'] > 4 * 1024 * 1024) {
		$error = 'Image is too large (maximum 4 MB).';
		return '';
	}
	$info = @getimagesize($file['tmp_name']);
	$allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif');
	if ($info === false || !isset($allowed[$info['mime']])) {
		$error = 'Only JPG, PNG, WEBP or GIF images are allowed.';
		return '';
	}
	$name = preg_replace('/[^a-z0-9]/i', '', $prefix) . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$info['mime']];
	if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../images/' . $name)) {
		$error = 'Could not save the image.';
		return '';
	}
	return $name;
}

// delete an uploaded picture, but never the bundled static images
function delete_uploaded_image($name) {
	if ($name == '' || strpos($name, '_') === false || strpos($name, 'jam_') === 0) return;
	$path = __DIR__ . '/../images/' . basename($name);
	if (is_file($path)) @unlink($path);
}

// ---------- icons (inline SVG, no external library needed) ----------
function icon($name) {
	static $i = array(
		'camera' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
		'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
		'package' => '<path d="M16.5 9.4 7.5 4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
		'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
		'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
		'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
		'calendar-plus' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/>',
		'calendar-check' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18m-12 6 2 2 4-4"/>',
		'bookings' => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 12h6M9 16h4"/>',
		'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
		'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.09-3.09a2 2 0 0 0-2.82 0L6 21"/>',
		'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
		'layers' => '<path d="m12 2 10 5-10 5L2 7z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/>',
		'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
		'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
		'chart' => '<path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/>',
		'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
		'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
		'printer' => '<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
		'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>',
		'moon' => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
		'menu' => '<path d="M3 12h18M3 6h18M3 18h18"/>',
		'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
		'plus' => '<path d="M12 5v14M5 12h14"/>',
		'check' => '<path d="M20 6 9 17l-5-5"/>',
		'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
		'edit' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>',
		'trash' => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
		'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
		'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
		'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/>',
		'eye' => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
		'eye-off' => '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><path d="m2 2 20 20"/>',
		'file' => '<path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>',
		'trend' => '<path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>',
		'wallet' => '<path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/>',
		'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
		'arrow' => '<path d="M5 12h14M12 5l7 7-7 7"/>',
		'chev-left' => '<path d="m15 18-6-6 6-6"/>',
		'chev-right' => '<path d="m9 18 6-6-6-6"/>',
		'alert' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
		'command' => '<path d="M18 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3H6a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3V6a3 3 0 0 0-3-3 3 3 0 0 0-3 3 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/>',
		'tag' => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5"/>',
		'zap' => '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
		'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>'
	);
	if (!isset($i[$name])) $name = 'info';
	return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $i[$name] . '</svg>';
}

// ---------- sidebar menu for every role ----------
function nav_items($role) {
	if ($role == 'customer') {
		return array(
			array('dashboard', 'Dashboard', 'view/customer/dashboard.php', 'dashboard'),
			array('packages', 'Packages', 'view/customer/packages.php', 'package'),
			array('photographers', 'Photographers', 'view/customer/photographers.php', 'users'),
			array('new-booking', 'New Booking', 'view/customer/new-booking.php', 'calendar-plus'),
			array('my-bookings', 'My Bookings', 'view/customer/my-bookings.php', 'bookings'),
			array('calendar', 'Calendar', 'view/calendar.php', 'calendar'),
			array('reviews', 'Reviews', 'view/customer/reviews.php', 'star'),
			array('complaints', 'Complaints', 'view/customer/complaints.php', 'message')
		);
	}
	if ($role == 'photographer') {
		return array(
			array('dashboard', 'Dashboard', 'view/photographer/dashboard.php', 'dashboard'),
			array('shoots', 'Assigned Shoots', 'view/photographer/shoots.php', 'bookings'),
			array('calendar', 'Calendar', 'view/calendar.php', 'calendar'),
			array('portfolio', 'Portfolio', 'view/photographer/portfolio.php', 'image'),
			array('availability', 'Availability', 'view/photographer/availability.php', 'calendar-check')
		);
	}
	if ($role == 'manager') {
		return array(
			array('dashboard', 'Dashboard', 'view/manager/dashboard.php', 'dashboard'),
			array('bookings', 'Booking Requests', 'view/manager/bookings.php', 'bookings'),
			array('calendar', 'Calendar', 'view/calendar.php', 'calendar'),
			array('packages', 'Packages', 'view/manager/packages.php', 'package')
		);
	}
	return array(
		array('dashboard', 'Dashboard', 'view/admin/dashboard.php', 'dashboard'),
		array('reports', 'Reports', 'view/admin/reports.php', 'chart'),
		array('users', 'Users', 'view/admin/users.php', 'users'),
		array('categories', 'Categories', 'view/admin/categories.php', 'layers'),
		array('calendar', 'Calendar', 'view/calendar.php', 'calendar'),
		array('complaints', 'Complaints', 'view/admin/complaints.php', 'message')
	);
}

// ---------- flash toasts ----------
function render_flash() {
	$out = '<div class="toast-stack" id="toastStack" aria-live="polite">';
	if (isset($_SESSION['message'])) {
		$out .= '<div class="toast toast-success">' . icon('check') . '<span>' . e($_SESSION['message']) . '</span></div>';
		unset($_SESSION['message']);
	}
	if (isset($_SESSION['error'])) {
		$out .= '<div class="toast toast-error">' . icon('alert') . '<span>' . e($_SESSION['error']) . '</span></div>';
		unset($_SESSION['error']);
	}
	if (isset($_SESSION['login_error'])) {
		$out .= '<div class="toast toast-error">' . icon('alert') . '<span>' . e($_SESSION['login_error']) . '</span></div>';
		unset($_SESSION['login_error']);
	}
	return $out . '</div>';
}

// ---------- pagination-free CSV download helper ----------
function csv_download($filename, $header, $rows) {
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	$out = fopen('php://output', 'w');
	fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Bangla/Unicode correctly
	fputcsv($out, $header);
	foreach ($rows as $r) {
		// stop spreadsheet formula injection
		foreach ($r as $k => $v) {
			if (is_string($v) && $v !== '' && strpos('=+-@', $v[0]) !== false) $r[$k] = "'" . $v;
		}
		fputcsv($out, $r);
	}
	fclose($out);
	exit();
}

// ---------- keep what the user typed when a form fails validation ----------
function set_old($data) {
	$_SESSION['old_input'] = $data;
}

function old($key, $default = '') {
	if (isset($_SESSION['old_input'][$key])) {
		return $_SESSION['old_input'][$key];
	}
	return $default;
}

function clear_old() {
	unset($_SESSION['old_input']);
}

// ---------- small validators used by several controllers ----------
function valid_date($d) {
	$dt = DateTime::createFromFormat('Y-m-d', (string)$d);
	return $dt && $dt->format('Y-m-d') === $d;
}

function valid_slot($s) {
	return in_array($s, array('Morning', 'Afternoon', 'Evening'), true);
}

// ---------- small UI pieces used by many views ----------
function pw_toggle() {
	return '<button type="button" class="pw-toggle" aria-label="Show or hide password"><span class="eye">' . icon('eye') . '</span><span class="eye-off" hidden>' . icon('eye-off') . '</span></button>';
}

// dashboard number card. $key links it to the AJAX refresh, $format = int | money | rating
function stat_card($label, $value, $iconName, $color = '', $key = '', $format = 'int') {
	$text = $format == 'money' ? money($value) : ($format == 'rating' ? number_format((float)$value, 1) : number_format((float)$value));
	return '<div class="stat ' . e($color) . '"><div class="stat-icon">' . icon($iconName) . '</div><div>'
		. '<div class="stat-number"' . ($key != '' ? ' data-stat="' . e($key) . '"' : '') . ' data-value="' . e($value) . '" data-format="' . e($format) . '">' . e($text) . '</div>'
		. '<div class="stat-label">' . e($label) . '</div></div></div>';
}

function empty_state($title, $text = '', $iconName = 'info') {
	return '<div class="empty">' . icon($iconName) . '<b>' . e($title) . '</b>' . e($text) . '</div>';
}

// booking progress bar: pending -> approved -> assigned -> completed
function booking_steps($status) {
	$labels = array('pending' => 'Requested', 'approved' => 'Approved', 'assigned' => 'Photographer', 'completed' => 'Completed');
	if ($status == 'rejected' || $status == 'cancelled') {
		return '<div class="steps"><div class="step done"><span class="step-dot">' . icon('check') . '</span><span>Requested</span><span class="step-line"></span></div>'
			. '<div class="step bad"><span class="step-dot">' . icon('x') . '</span><span>' . ucfirst($status) . '</span></div></div>';
	}
	$order = array_keys($labels);
	$at = array_search($status, $order);
	$out = '<div class="steps">';
	foreach ($order as $i => $key) {
		$cls = $i < $at ? 'done' : ($i == $at ? ($status == 'completed' ? 'done' : 'now') : '');
		$dot = $cls == 'done' ? icon('check') : ($i + 1);
		$out .= '<div class="step ' . $cls . '"><span class="step-dot">' . $dot . '</span><span>' . $labels[$key] . '</span>'
			. ($i < count($order) - 1 ? '<span class="step-line"></span>' : '') . '</div>';
	}
	return $out . '</div>';
}
