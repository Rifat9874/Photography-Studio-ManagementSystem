<?php
require_once __DIR__ . '/BaseModel.php';

class Category extends BaseModel {
	public function insertCategory($name, $description) {
		return $this->exec("INSERT INTO categories (name, description) VALUES (?, ?)", 'ss', array($name, $description));
	}

	public function getAllCategories() {
		return $this->all("SELECT * FROM categories ORDER BY name");
	}

	// categories with how many packages / bookings use them
	public function getAllWithCounts() {
		return $this->all("SELECT c.*,
				(SELECT COUNT(*) FROM packages p WHERE p.category_id = c.id) AS package_count,
				(SELECT COUNT(*) FROM bookings b JOIN packages p ON b.package_id = p.id WHERE p.category_id = c.id) AS booking_count
			FROM categories c ORDER BY c.name");
	}

	public function getCategoryById($id) {
		return $this->one("SELECT * FROM categories WHERE id = ?", 'i', array((int)$id));
	}

	public function countBookings($id) {
		return (int)$this->value("SELECT COUNT(*) FROM bookings b JOIN packages p ON b.package_id = p.id WHERE p.category_id = ?", 'i', array((int)$id));
	}

	public function updateCategory($id, $name, $description) {
		return $this->exec("UPDATE categories SET name = ?, description = ? WHERE id = ?", 'ssi', array($name, $description, (int)$id));
	}

	public function deleteCategory($id) {
		return $this->exec("DELETE FROM categories WHERE id = ?", 'i', array((int)$id));
	}
}
