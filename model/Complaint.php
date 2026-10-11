<?php
require_once __DIR__ . '/BaseModel.php';

class Complaint extends BaseModel {
	public function insertComplaint($customerId, $bookingId, $subject, $message) {
		return $this->exec("INSERT INTO complaints (customer_id, booking_id, subject, message, status) VALUES (?, ?, ?, ?, 'open')",
			'iiss', array((int)$customerId, $bookingId, $subject, $message));
	}

	public function getComplaintsByCustomer($customerId) {
		return $this->all("SELECT * FROM complaints WHERE customer_id = ? ORDER BY id DESC", 'i', array((int)$customerId));
	}

	public function getAllComplaints() {
		return $this->all("SELECT co.*, u.name AS customer_name, u.email AS customer_email, u.profile_pic AS customer_pic
			FROM complaints co JOIN users u ON co.customer_id = u.id ORDER BY co.id DESC");
	}

	public function getComplaintById($id) {
		return $this->one("SELECT * FROM complaints WHERE id = ?", 'i', array((int)$id));
	}

	public function countByStatus($status) {
		return (int)$this->value("SELECT COUNT(*) FROM complaints WHERE status = ?", 's', array($status));
	}

	public function updateComplaint($id, $status, $adminReply) {
		return $this->exec("UPDATE complaints SET status = ?, admin_reply = ? WHERE id = ?", 'ssi', array($status, $adminReply, (int)$id));
	}

	public function deleteComplaint($id) {
		return $this->exec("DELETE FROM complaints WHERE id = ?", 'i', array((int)$id));
	}
}
