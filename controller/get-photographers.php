<?php
// AJAX + JSON endpoint - photographers who are free on the given date (and slot)
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/User.php';

header('Content-Type: application/json');
if (empty($_SESSION['user_id'])) {
	http_response_code(401);
	echo json_encode(array('photographers' => array(), 'message' => 'Please login first'));
	exit();
}

$date = isset($_GET['date']) ? $_GET['date'] : '';
$slot = isset($_GET['slot']) ? $_GET['slot'] : '';
$exclude = isset($_GET['exclude']) ? (int)$_GET['exclude'] : 0;

if (!valid_date($date) || ($slot != '' && !valid_slot($slot))) {
	echo json_encode(array('photographers' => array()));
	exit();
}

$user = new User();
$list = $user->getAvailablePhotographers($date, $slot, $exclude);

// only send what the page needs (no phone / email)
$out = array();
foreach ($list as $p) {
	$out[] = array(
		'id' => (int)$p['id'],
		'name' => $p['name'],
		'specialization' => $p['specialization'],
		'time_slot' => $p['time_slot'],
		'avg_rating' => $p['avg_rating'],
		'review_count' => (int)$p['review_count'],
		'busy' => (int)$p['busy']
	);
}
echo json_encode(array('photographers' => $out));
