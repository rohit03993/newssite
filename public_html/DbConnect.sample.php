<?php
	class DbConnect {
		private $host = 'localhost';
		private $dbName = 'PUT_HOSTINGER_DB_NAME';
		private $user = 'PUT_HOSTINGER_DB_USER';
		private $pass = 'PUT_HOSTINGER_DB_PASSWORD';

		public function connect() {
			try {
				$conn = new PDO('mysql:host=' . $this->host . '; dbname=' . $this->dbName, $this->user, $this->pass);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				return $conn;
			} catch( PDOException $e) {
				echo 'Database Error: ' . $e->getMessage();
			}
		}
	}
 ?>
