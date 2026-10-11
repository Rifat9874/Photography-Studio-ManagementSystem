<?php
// Database connection class (same database and credentials as before).
// The connection is created once and reused by every model in the request.
class DBConnection {
	private $db_host = 'localhost';
	private $db_username = 'root';
	private $db_password = '';
	private $db_name = 'photography_studio';

	private static $connection = null;

	public function connect() {
		if (self::$connection === null) {
			self::$connection = new mysqli(
				$this->db_host,
				$this->db_username,
				$this->db_password,
				$this->db_name
			);
			self::$connection->set_charset('utf8mb4');
		}
		return self::$connection;
	}
}
