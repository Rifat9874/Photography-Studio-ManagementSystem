<?php
require_once __DIR__ . '/BaseModel.php';

class Availability extends BaseModel {
	public function insertSlot($photographerId, $availableDate, $timeSlot) {
		return $this->exec("INSERT INTO availability (photographer_id, available_date, time_slot) VALUES (?, ?, ?)",
			'iss', array((int)$photographerId, $availableDate, $timeSlot));
	}

	public function getSlotsByPhotographer($photographerId) {
		return $this->all("SELECT * FROM availability WHERE photographer_id = ? ORDER BY available_date, FIELD(time_slot, 'Morning', 'Afternoon', 'Evening')",
			'i', array((int)$photographerId));
	}

	// every free slot + whether a booking already uses it ('booked' = 1)
	public function getSlotsWithBookingState($photographerId) {
		return $this->all("SELECT a.*,
				(SELECT COUNT(*) FROM bookings b WHERE b.photographer_id = a.photographer_id AND b.booking_date = a.available_date
					AND b.booking_time = a.time_slot AND b.status IN ('assigned', 'completed')) AS booked
			FROM availability a
			WHERE a.photographer_id = ?
			ORDER BY a.available_date, FIELD(a.time_slot, 'Morning', 'Afternoon', 'Evening')",
			'i', array((int)$photographerId));
	}

	public function getSlotById($id) {
		return $this->one("SELECT * FROM availability WHERE id = ?", 'i', array((int)$id));
	}

	// stops the same date + slot being added twice
	public function slotExists($photographerId, $availableDate, $timeSlot, $exceptId = 0) {
		return $this->one("SELECT id FROM availability WHERE photographer_id = ? AND available_date = ? AND time_slot = ? AND id <> ?",
			'issi', array((int)$photographerId, $availableDate, $timeSlot, (int)$exceptId)) != null;
	}

	public function updateSlot($id, $availableDate, $timeSlot) {
		return $this->exec("UPDATE availability SET available_date = ?, time_slot = ? WHERE id = ?",
			'ssi', array($availableDate, $timeSlot, (int)$id));
	}

	public function deleteSlot($id) {
		return $this->exec("DELETE FROM availability WHERE id = ?", 'i', array((int)$id));
	}
}
