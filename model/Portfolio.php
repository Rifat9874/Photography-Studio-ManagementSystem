<?php
require_once __DIR__ . '/BaseModel.php';

class Portfolio extends BaseModel {
	public function insertItem($photographerId, $categoryId, $title, $description, $image) {
		return $this->exec("INSERT INTO portfolio (photographer_id, category_id, title, description, image) VALUES (?, ?, ?, ?, ?)",
			'iisss', array((int)$photographerId, (int)$categoryId, $title, $description, $image));
	}

	public function getItemsByPhotographer($photographerId) {
		return $this->all("SELECT pf.*, c.name AS category_name FROM portfolio pf
			JOIN categories c ON pf.category_id = c.id
			WHERE pf.photographer_id = ? ORDER BY pf.id DESC", 'i', array((int)$photographerId));
	}

	// everything (customers browse this)
	public function getAllItems() {
		return $this->all("SELECT pf.*, c.name AS category_name, u.name AS photographer_name, u.specialization
			FROM portfolio pf
			JOIN categories c ON pf.category_id = c.id
			JOIN users u ON pf.photographer_id = u.id
			ORDER BY pf.id DESC");
	}

	public function getItemById($id) {
		return $this->one("SELECT * FROM portfolio WHERE id = ?", 'i', array((int)$id));
	}

	public function updateItem($id, $categoryId, $title, $description) {
		return $this->exec("UPDATE portfolio SET category_id = ?, title = ?, description = ? WHERE id = ?",
			'issi', array((int)$categoryId, $title, $description, (int)$id));
	}

	public function updateImage($id, $image) {
		return $this->exec("UPDATE portfolio SET image = ? WHERE id = ?", 'si', array($image, (int)$id));
	}

	public function deleteItem($id) {
		return $this->exec("DELETE FROM portfolio WHERE id = ?", 'i', array((int)$id));
	}
}
