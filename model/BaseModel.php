<?php
require_once __DIR__ . '/../db/db.php';

// Small base class: every model extends it so the SQL helpers are written only once.
class BaseModel {
	public function establishConnection() {
		$db = new DBConnection();
		return $db->connect();
	}

	// run a prepared statement and return it
	protected function run($sql, $types = '', $params = array()) {
		$conn = $this->establishConnection();
		$stmt = $conn->prepare($sql);
		if ($types !== '') {
			$stmt->bind_param($types, ...$params);
		}
		$stmt->execute();
		return $stmt;
	}

	// SELECT many rows
	protected function all($sql, $types = '', $params = array()) {
		$stmt = $this->run($sql, $types, $params);
		$result = $stmt->get_result();
		$rows = array();
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
		return $rows;
	}

	// SELECT one row (or null)
	protected function one($sql, $types = '', $params = array()) {
		$rows = $this->all($sql, $types, $params);
		return count($rows) > 0 ? $rows[0] : null;
	}

	// SELECT a single value
	protected function value($sql, $types = '', $params = array()) {
		$row = $this->one($sql, $types, $params);
		if ($row == null) return 0;
		return reset($row);
	}

	// INSERT / UPDATE / DELETE
	protected function exec($sql, $types = '', $params = array()) {
		$stmt = $this->run($sql, $types, $params);
		return $stmt->affected_rows >= 0;
	}
}
