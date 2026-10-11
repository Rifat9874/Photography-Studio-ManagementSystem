<?php
require_once __DIR__ . '/BaseModel.php';

class Review extends BaseModel {
	public function insertReview($bookingId, $customerId, $rating, $comment) {
		return $this->exec("INSERT INTO reviews (booking_id, customer_id, rating, comment) VALUES (?, ?, ?, ?)",
			'iiis', array((int)$bookingId, (int)$customerId, (int)$rating, $comment));
	}

	public function getReviewById($id) {
		return $this->one("SELECT * FROM reviews WHERE id = ?", 'i', array((int)$id));
	}

	public function getReviewByBooking($bookingId) {
		return $this->one("SELECT * FROM reviews WHERE booking_id = ?", 'i', array((int)$bookingId));
	}

	public function getReviewsByCustomer($customerId) {
		return $this->all("SELECT r.*, p.name AS package_name, b.booking_date, u.name AS photographer_name
			FROM reviews r
			JOIN bookings b ON r.booking_id = b.id
			JOIN packages p ON b.package_id = p.id
			LEFT JOIN users u ON b.photographer_id = u.id
			WHERE r.customer_id = ? ORDER BY r.id DESC", 'i', array((int)$customerId));
	}

	// reviews of the shoots done by one photographer
	public function getReviewsByPhotographer($photographerId) {
		return $this->all("SELECT r.*, p.name AS package_name, cu.name AS customer_name, b.booking_date
			FROM reviews r
			JOIN bookings b ON r.booking_id = b.id
			JOIN packages p ON b.package_id = p.id
			JOIN users cu ON r.customer_id = cu.id
			WHERE b.photographer_id = ? ORDER BY r.id DESC", 'i', array((int)$photographerId));
	}

	public function getAllReviews() {
		return $this->all("SELECT r.*, p.name AS package_name, cu.name AS customer_name
			FROM reviews r
			JOIN bookings b ON r.booking_id = b.id
			JOIN packages p ON b.package_id = p.id
			JOIN users cu ON r.customer_id = cu.id
			ORDER BY r.id DESC");
	}

	// average + how many 1..5 star reviews a photographer has
	public function getPhotographerSummary($photographerId) {
		$rows = $this->getReviewsByPhotographer($photographerId);
		$dist = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);
		$sum = 0;
		foreach ($rows as $r) {
			$dist[(int)$r['rating']]++;
			$sum += (int)$r['rating'];
		}
		$count = count($rows);
		return array('count' => $count, 'avg' => $count > 0 ? round($sum / $count, 1) : 0, 'dist' => $dist);
	}

	public function updateReview($id, $rating, $comment) {
		return $this->exec("UPDATE reviews SET rating = ?, comment = ? WHERE id = ?", 'isi', array((int)$rating, $comment, (int)$id));
	}

	public function deleteReview($id) {
		return $this->exec("DELETE FROM reviews WHERE id = ?", 'i', array((int)$id));
	}
}
