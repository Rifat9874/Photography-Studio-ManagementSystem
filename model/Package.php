<?php
require_once __DIR__ . '/BaseModel.php';

class Package extends BaseModel {
	public function insertPackage($categoryId, $name, $price, $duration, $inclusions) {
		return $this->exec("INSERT INTO packages (category_id, name, price, duration, inclusions) VALUES (?, ?, ?, ?, ?)",
			'isdss', array((int)$categoryId, $name, $price, $duration, $inclusions));
	}

	// all packages with their category name and how often they were booked
	public function getAllPackages() {
		return $this->all("SELECT p.*, c.name AS category_name,
				(SELECT COUNT(*) FROM bookings b WHERE b.package_id = p.id) AS booking_count
			FROM packages p
			JOIN categories c ON p.category_id = c.id
			ORDER BY c.name, p.price");
	}

	// used by AJAX when the customer picks a category
	public function getPackagesByCategory($categoryId) {
		return $this->all("SELECT * FROM packages WHERE category_id = ? ORDER BY price", 'i', array((int)$categoryId));
	}

	public function getPackageById($id) {
		return $this->one("SELECT p.*, c.name AS category_name FROM packages p JOIN categories c ON p.category_id = c.id WHERE p.id = ?",
			'i', array((int)$id));
	}

	public function countBookings($id) {
		return (int)$this->value("SELECT COUNT(*) FROM bookings WHERE package_id = ?", 'i', array((int)$id));
	}

	public function updatePackage($id, $categoryId, $name, $price, $duration, $inclusions) {
		return $this->exec("UPDATE packages SET category_id = ?, name = ?, price = ?, duration = ?, inclusions = ? WHERE id = ?",
			'isdssi', array((int)$categoryId, $name, $price, $duration, $inclusions, (int)$id));
	}

	public function deletePackage($id) {
		return $this->exec("DELETE FROM packages WHERE id = ?", 'i', array((int)$id));
	}
}
