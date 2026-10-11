<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../model/Availability.php';

require_role('photographer');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	redirect('view/photographer/availability.php');
}
csrf_verify('view/photographer/availability.php');

$action = isset($_POST['action']) ? $_POST['action'] : '';
$availability = new Availability();
$photographerId = $_SESSION['user_id'];

// CREATE - one date or a whole date range, one or more time slots at once
if ($action == 'create') {
	$start = isset($_POST['available_date']) ? $_POST['available_date'] : '';
	$end = (isset($_POST['end_date']) && $_POST['end_date'] != '') ? $_POST['end_date'] : $start;
	$slots = isset($_POST['slots']) && is_array($_POST['slots']) ? $_POST['slots'] : array();

	if (!valid_date($start) || !valid_date($end)) {
		flash_error("Please choose a valid date");
	} else if ($start < date('Y-m-d')) {
		flash_error("You cannot add a past date");
	} else if ($end < $start) {
		flash_error("The end date must be after the start date");
	} else if (count($slots) == 0) {
		flash_error("Please choose at least one time slot");
	} else if ((strtotime($end) - strtotime($start)) / 86400 > 60) {
		flash_error("You can add at most 60 days at a time");
	} else {
		$skipFriday = isset($_POST['skip_friday']);
		$added = 0;
		for ($t = strtotime($start); $t <= strtotime($end); $t = strtotime('+1 day', $t)) {
			if ($skipFriday && date('N', $t) == 5) continue;
			$day = date('Y-m-d', $t);
			foreach ($slots as $slot) {
				if (valid_slot($slot) && !$availability->slotExists($photographerId, $day, $slot)) {
					$availability->insertSlot($photographerId, $day, $slot);
					$added++;
				}
			}
		}
		if ($added > 0) {
			flash_success($added . " availability slot(s) added");
		} else {
			flash_error("Those slots are already in your list");
		}
	}
}

// UPDATE
if ($action == 'update') {
	$id = (int)$_POST['id'];
	$availableDate = $_POST['available_date'];
	$timeSlot = $_POST['time_slot'];
	$row = $availability->getSlotById($id);

	if ($row == null || $row['photographer_id'] != $photographerId) {
		flash_error("This slot does not belong to you");
	} else if (!valid_date($availableDate) || !valid_slot($timeSlot)) {
		flash_error("Please choose a valid date and slot");
	} else if ($availableDate < date('Y-m-d')) {
		flash_error("You cannot move a slot to a past date");
	} else if ($availability->slotExists($photographerId, $availableDate, $timeSlot, $id)) {
		flash_error("This date and slot is already in your list");
	} else {
		$availability->updateSlot($id, $availableDate, $timeSlot);
		flash_success("Availability updated");
	}
}

// DELETE
if ($action == 'delete') {
	$row = $availability->getSlotById((int)$_POST['id']);
	if ($row != null && $row['photographer_id'] == $photographerId) {
		$availability->deleteSlot($row['id']);
		flash_success("Availability removed");
	} else {
		flash_error("This slot does not belong to you");
	}
}
redirect('view/photographer/availability.php');
