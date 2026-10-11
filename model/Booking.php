<?php
require_once __DIR__ . '/BaseModel.php';

class Booking extends BaseModel {

	// the shared SELECT part used by most queries
	private $baseSelect = "SELECT b.*, p.name AS package_name, p.price, p.duration, p.inclusions, p.category_id,
				c.name AS category_name,
				cu.name AS customer_name, cu.phone AS customer_phone, cu.email AS customer_email,
				u.name AS photographer_name, u.phone AS photographer_phone, u.profile_pic AS photographer_pic
			FROM bookings b
			JOIN packages p ON b.package_id = p.id
			JOIN categories c ON p.category_id = c.id
			JOIN users cu ON b.customer_id = cu.id
			LEFT JOIN users u ON b.photographer_id = u.id ";

	// CREATE - customer sends a booking request
	public function insertBooking($customerId, $packageId, $bookingDate, $bookingTime, $location, $note) {
		try {
			$conn = $this->establishConnection();
			$stmt = $conn->prepare("INSERT INTO bookings (customer_id, package_id, booking_date, booking_time, location, note, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
			$stmt->bind_param('iissss', $customerId, $packageId, $bookingDate, $bookingTime, $location, $note);
			$stmt->execute();
			return $conn->insert_id;
		} catch (mysqli_sql_exception $ex) {
			return $ex->getMessage();
		}
	}

	// READ - bookings of one customer
	public function getBookingsByCustomer($customerId) {
		return $this->all($this->baseSelect . "WHERE b.customer_id = ? ORDER BY b.booking_date DESC, b.id DESC", 'i', array((int)$customerId));
	}

	// READ - all bookings for the booking manager / admin
	public function getAllBookings() {
		return $this->all($this->baseSelect . "ORDER BY b.id DESC");
	}

	// READ - shoots assigned to one photographer
	public function getBookingsByPhotographer($photographerId) {
		return $this->all($this->baseSelect . "WHERE b.photographer_id = ? ORDER BY b.booking_date DESC", 'i', array((int)$photographerId));
	}

	public function getBookingById($id) {
		return $this->one($this->baseSelect . "WHERE b.id = ?", 'i', array((int)$id));
	}

	public function countByStatus($status) {
		return (int)$this->value("SELECT COUNT(*) FROM bookings WHERE status = ?", 's', array($status));
	}

	// does this customer already have an active booking in the same date + slot?
	public function customerHasSlot($customerId, $date, $time, $exceptId = 0) {
		return $this->one("SELECT id FROM bookings WHERE customer_id = ? AND booking_date = ? AND booking_time = ?
			AND status NOT IN ('cancelled', 'rejected') AND id <> ?", 'issi', array((int)$customerId, $date, $time, (int)$exceptId)) != null;
	}

	// is the photographer already working on another shoot in the same date + slot?
	public function photographerBusy($photographerId, $date, $time, $exceptId = 0) {
		return $this->one("SELECT id FROM bookings WHERE photographer_id = ? AND booking_date = ? AND booking_time = ?
			AND status IN ('assigned', 'completed') AND id <> ?", 'issi', array((int)$photographerId, $date, $time, (int)$exceptId)) != null;
	}

	// photographer free-slot check used before assigning
	public function photographerIsFree($photographerId, $date, $time) {
		return $this->one("SELECT id FROM availability WHERE photographer_id = ? AND available_date = ? AND time_slot = ?",
			'iss', array((int)$photographerId, $date, $time)) != null;
	}

	// bookings for the calendar page (date range)
	public function getForCalendar($role, $userId, $from, $to) {
		if ($role == 'customer') {
			return $this->all($this->baseSelect . "WHERE b.customer_id = ? AND b.booking_date BETWEEN ? AND ? ORDER BY b.booking_date", 'iss', array((int)$userId, $from, $to));
		}
		if ($role == 'photographer') {
			return $this->all($this->baseSelect . "WHERE b.photographer_id = ? AND b.booking_date BETWEEN ? AND ? ORDER BY b.booking_date", 'iss', array((int)$userId, $from, $to));
		}
		return $this->all($this->baseSelect . "WHERE b.booking_date BETWEEN ? AND ? ORDER BY b.booking_date", 'ss', array($from, $to));
	}

	public function updateBooking($id, $packageId, $bookingDate, $bookingTime, $location, $note) {
		return $this->exec("UPDATE bookings SET package_id = ?, booking_date = ?, booking_time = ?, location = ?, note = ? WHERE id = ?",
			'issssi', array((int)$packageId, $bookingDate, $bookingTime, $location, $note, (int)$id));
	}

	public function updateStatus($id, $status) {
		return $this->exec("UPDATE bookings SET status = ? WHERE id = ?", 'si', array($status, (int)$id));
	}

	// manager assigns a photographer
	public function assignPhotographer($id, $photographerId) {
		return $this->exec("UPDATE bookings SET photographer_id = ?, status = 'assigned' WHERE id = ?", 'ii', array((int)$photographerId, (int)$id));
	}

	public function deleteBooking($id) {
		return $this->exec("DELETE FROM bookings WHERE id = ?", 'i', array((int)$id));
	}
}
