<?php
/**
 * Author: Manish
 * Version: v0.2 alpha
 */
class DataDB {
	const DB_TYPE_MYSQL = 1;

	public $db_type;

	private static $instance = null;

	public function __construct(){
		$this->db_type = self::DB_TYPE_MYSQL;
	}

	public static function connect(){
		try{
			if (!self::$instance)
			{
				$dsn = 'mysql:dbname='.Configuration::db.';host='.Configuration::host;
				$user = Configuration::user;
				$password = Configuration::pass;
				self::$instance = new PDO($dsn, $user, $password);
				self::$instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			return self::$instance;
		}catch(PDOException $e){
			die ("Unable To Connect" . $e->getMessage());
		}
	}
}