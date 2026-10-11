<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {

	// -------- passwords: hashed, but old plain-text rows in the database still work --------
	public function verifyPassword($row, $password) {
		$stored = (string)$row['password'];
		$info = password_get_info($stored);
		if ($info['algo'] !== null && $info['algo'] !== 0) {
			return password_verify($password, $stored);
		}
		return hash_equals($stored, (string)$password); // legacy plain-text password
	}

	// CREATE - new account
	public function insertUser($name, $username, $email, $password, $phone, $role, $specialization) {
		try {
			$hash = password_hash($password, PASSWORD_DEFAULT);
			$conn = $this->establishConnection();
			$stmt = $conn->prepare("INSERT INTO users (name, username, email, password, phone, role, specialization) VALUES (?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param('sssssss', $name, $username, $email, $hash, $phone, $role, $specialization);
			$stmt->execute();
			return $conn->insert_id;
		} catch (mysqli_sql_exception $ex) {
			return $ex->getMessage();
		}
	}

	// login check - user can type either username or email
	public function loginCheck($loginId, $password) {
		$row = $this->one("SELECT * FROM users WHERE username = ? OR email = ?", 'ss', array($loginId, $loginId));
		if ($row != null && $this->verifyPassword($row, $password)) {
			// upgrade an old plain-text password to a secure hash on first successful login
			$info = password_get_info((string)$row['password']);
			if ($info['algo'] === null || $info['algo'] === 0) {
				$this->updatePassword($row['id'], $password);
			}
			return $row;
		}
		return null;
	}

	public function isUsernameTaken($username) {
		return $this->one("SELECT id FROM users WHERE username = ?", 's', array($username)) != null;
	}

	public function isEmailTaken($email, $exceptId = 0) {
		return $this->one("SELECT id FROM users WHERE email = ? AND id <> ?", 'si', array($email, $exceptId)) != null;
	}

	public function getUserById($id) {
		return $this->one("SELECT * FROM users WHERE id = ?", 'i', array((int)$id));
	}

	// used by the "forgot password" page
	public function getUserByUsernameAndEmail($username, $email) {
		return $this->one("SELECT * FROM users WHERE username = ? AND email = ?", 'ss', array($username, $email));
	}

	public function getAllUsers() {
		return $this->all("SELECT id, name, username, email, phone, role, specialization, status, profile_pic, created_at FROM users ORDER BY id");
	}

	public function getUsersByRole($role) {
		return $this->all("SELECT * FROM users WHERE role = ? AND status = 'active' ORDER BY name", 's', array($role));
	}

	public function countByRole($role) {
		return (int)$this->value("SELECT COUNT(*) FROM users WHERE role = ?", 's', array($role));
	}

	// photographers with rating + workload numbers (customer "Photographers" page)
	public function getPhotographersWithStats() {
		$sql = "SELECT u.id, u.name, u.email, u.phone, u.specialization, u.profile_pic, u.created_at,
					(SELECT ROUND(AVG(r.rating), 1) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.photographer_id = u.id) AS avg_rating,
					(SELECT COUNT(*) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.photographer_id = u.id) AS review_count,
					(SELECT COUNT(*) FROM bookings b WHERE b.photographer_id = u.id AND b.status = 'completed') AS completed_shoots,
					(SELECT COUNT(*) FROM portfolio p WHERE p.photographer_id = u.id) AS portfolio_count
				FROM users u
				WHERE u.role = 'photographer' AND u.status = 'active'
				ORDER BY u.name";
		return $this->all($sql);
	}

	// photographers who marked themselves free on this date (used by AJAX + assign page)
	// 'busy' = 1 when the photographer already has another shoot in the same date + slot
	public function getAvailablePhotographers($date, $slot = '', $excludeBookingId = 0) {
		$sql = "SELECT u.id, u.name, u.specialization, u.profile_pic, a.time_slot,
					(SELECT ROUND(AVG(r.rating), 1) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.photographer_id = u.id) AS avg_rating,
					(SELECT COUNT(*) FROM reviews r JOIN bookings b ON r.booking_id = b.id WHERE b.photographer_id = u.id) AS review_count,
					(SELECT COUNT(*) FROM bookings bb WHERE bb.photographer_id = u.id AND bb.booking_date = a.available_date
						AND bb.booking_time = a.time_slot AND bb.status IN ('assigned', 'completed') AND bb.id <> ?) AS busy
				FROM users u
				JOIN availability a ON u.id = a.photographer_id
				WHERE u.role = 'photographer' AND u.status = 'active' AND a.available_date = ?";
		$types = 'is';
		$params = array((int)$excludeBookingId, $date);
		if ($slot != '') {
			$sql .= " AND a.time_slot = ?";
			$types .= 's';
			$params[] = $slot;
		}
		$sql .= " ORDER BY u.name, a.time_slot";
		return $this->all($sql, $types, $params);
	}

	// UPDATE - profile
	public function updateProfile($id, $name, $email, $phone, $specialization) {
		return $this->exec("UPDATE users SET name = ?, email = ?, phone = ?, specialization = ? WHERE id = ?",
			'ssssi', array($name, $email, $phone, $specialization, (int)$id));
	}

	public function updateProfilePic($id, $fileName) {
		return $this->exec("UPDATE users SET profile_pic = ? WHERE id = ?", 'si', array($fileName, (int)$id));
	}

	public function updatePassword($id, $newPassword) {
		$hash = password_hash($newPassword, PASSWORD_DEFAULT);
		return $this->exec("UPDATE users SET password = ? WHERE id = ?", 'si', array($hash, (int)$id));
	}

	// UPDATE - admin activates / deactivates an account
	public function updateStatus($id, $status) {
		return $this->exec("UPDATE users SET status = ? WHERE id = ?", 'si', array($status, (int)$id));
	}

	// DELETE
	public function deleteUser($id) {
		return $this->exec("DELETE FROM users WHERE id = ?", 'i', array((int)$id));
	}
}
