<?php
require_once __DIR__ . '/BaseModel.php';

// Numbers for the dashboards and the admin reports page.
// "Confirmed revenue" = approved + assigned + completed bookings. "Collected" = completed only.
class Report extends BaseModel {

	public function statusCounts() {
		$counts = array('pending' => 0, 'approved' => 0, 'assigned' => 0, 'completed' => 0, 'rejected' => 0, 'cancelled' => 0);
		$rows = $this->all("SELECT status, COUNT(*) AS total FROM bookings GROUP BY status");
		foreach ($rows as $r) {
			$counts[$r['status']] = (int)$r['total'];
		}
		return $counts;
	}

	public function revenueTotals() {
		$row = $this->one("SELECT
				COALESCE(SUM(CASE WHEN b.status IN ('approved','assigned','completed') THEN p.price END), 0) AS confirmed,
				COALESCE(SUM(CASE WHEN b.status = 'completed' THEN p.price END), 0) AS collected,
				COALESCE(SUM(CASE WHEN b.status = 'pending' THEN p.price END), 0) AS pipeline
			FROM bookings b JOIN packages p ON b.package_id = p.id");
		return array('confirmed' => (float)$row['confirmed'], 'collected' => (float)$row['collected'], 'pipeline' => (float)$row['pipeline']);
	}

	// last N months (by shoot date), months without bookings are filled with 0
	public function byMonth($months = 6) {
		$out = array();
		for ($i = $months - 1; $i >= 0; $i--) {
			$key = date('Y-m', strtotime(date('Y-m-01') . " -$i month"));
			$out[$key] = array('label' => date('M y', strtotime($key . '-01')), 'revenue' => 0, 'bookings' => 0);
		}
		$from = array_keys($out)[0] . '-01';
		$rows = $this->all("SELECT DATE_FORMAT(b.booking_date, '%Y-%m') AS ym, COUNT(*) AS total,
				COALESCE(SUM(CASE WHEN b.status IN ('approved','assigned','completed') THEN p.price END), 0) AS revenue
			FROM bookings b JOIN packages p ON b.package_id = p.id
			WHERE b.booking_date >= ? GROUP BY ym", 's', array($from));
		foreach ($rows as $r) {
			if (isset($out[$r['ym']])) {
				$out[$r['ym']]['revenue'] = (float)$r['revenue'];
				$out[$r['ym']]['bookings'] = (int)$r['total'];
			}
		}
		return array_values($out);
	}

	public function byCategory() {
		return $this->all("SELECT c.name, COUNT(b.id) AS bookings,
				COALESCE(SUM(CASE WHEN b.status IN ('approved','assigned','completed') THEN p.price END), 0) AS revenue
			FROM categories c
			LEFT JOIN packages p ON p.category_id = c.id
			LEFT JOIN bookings b ON b.package_id = p.id
			GROUP BY c.id, c.name ORDER BY revenue DESC");
	}

	public function topPackages($limit = 5) {
		return $this->all("SELECT p.name, c.name AS category_name, COUNT(b.id) AS bookings
			FROM packages p JOIN categories c ON p.category_id = c.id
			LEFT JOIN bookings b ON b.package_id = p.id
			GROUP BY p.id, p.name, c.name ORDER BY bookings DESC, p.name LIMIT " . (int)$limit);
	}

	public function topPhotographers($limit = 5) {
		return $this->all("SELECT u.id, u.name, u.specialization, u.profile_pic,
				(SELECT COUNT(*) FROM bookings b WHERE b.photographer_id = u.id AND b.status = 'completed') AS completed,
				(SELECT COUNT(*) FROM bookings b WHERE b.photographer_id = u.id) AS total_shoots,
				(SELECT ROUND(AVG(r.rating), 1) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.photographer_id = u.id) AS avg_rating,
				(SELECT COUNT(*) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.photographer_id = u.id) AS review_count
			FROM users u WHERE u.role = 'photographer'
			ORDER BY completed DESC, avg_rating DESC LIMIT " . (int)$limit);
	}

	public function averageRating() {
		return (float)$this->value("SELECT COALESCE(ROUND(AVG(rating), 1), 0) FROM reviews");
	}

	public function totalReviews() {
		return (int)$this->value("SELECT COUNT(*) FROM reviews");
	}

	// money a customer has spent (not counting cancelled / rejected requests)
	public function customerSpend($customerId) {
		return (float)$this->value("SELECT COALESCE(SUM(p.price), 0) FROM bookings b JOIN packages p ON b.package_id = p.id
			WHERE b.customer_id = ? AND b.status NOT IN ('cancelled', 'rejected')", 'i', array((int)$customerId));
	}

	// statuses of one customer / photographer for their own doughnut chart
	public function statusCountsFor($column, $userId) {
		$col = ($column == 'photographer_id') ? 'photographer_id' : 'customer_id';
		$counts = array('pending' => 0, 'approved' => 0, 'assigned' => 0, 'completed' => 0, 'rejected' => 0, 'cancelled' => 0);
		$rows = $this->all("SELECT status, COUNT(*) AS total FROM bookings WHERE $col = ? GROUP BY status", 'i', array((int)$userId));
		foreach ($rows as $r) {
			$counts[$r['status']] = (int)$r['total'];
		}
		return $counts;
	}

	public function photographerEarnings($photographerId) {
		return (float)$this->value("SELECT COALESCE(SUM(p.price), 0) FROM bookings b JOIN packages p ON b.package_id = p.id
			WHERE b.photographer_id = ? AND b.status = 'completed'", 'i', array((int)$photographerId));
	}
}
