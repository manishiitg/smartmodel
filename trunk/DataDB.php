<?php
/**
 *
 * @author Manish <manish@excellencetechnoloiges.in>
 * @version v0.2
 * @package SmartModel
 * @copyright Copyright (c) 2009, Excellence Technologies
 *
 */
class DataDB {
	const DB_TYPE_MYSQL = 1;
	const DB_TYPE_MSSQL = 2;
	const DB_TYPE_WORDPRESS = 3;


	const USE_PDO = false;
	const DB_TYPE = self::DB_TYPE_MYSQL;

	private static $instance = null;

	public function __construct(){
	}

	public static function connect(){

		if (!self::$instance)
		{
			if(self::DB_TYPE == self::DB_TYPE_WORDPRESS){
				global $wpdb;
				self::$instance = $wpdb;
			}else{
				if(self::USE_PDO){
					try{
						if(self::DB_TYPE == self::DB_TYPE_MYSQL){
							$dsn = 'mysql:dbname='.Configuration::db.';host='.Configuration::host;
							$user = Configuration::user;
							$password = Configuration::pass;
							self::$instance = new PDO($dsn, $user, $password);
							self::$instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						}else if(self::DB_TYPE == self::DB_TYPE_MSSQL){
							self::$instance = new PDO("mssql:host=".Configuration::host.";dbname=".Configuration::db, Configuration::user ,Configuration::pass );
							self::$instance-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						}
					}catch(PDOException $e){
						die ("Unable To Connect" . $e->getMessage());
					}
				}else{
					$db = mysql_connect(Configuration::host,Configuration::user,Configuration::pass);
					mysql_selectdb(Configuration::db,$db);
					self::$instance = $db;
				}
			}
		}
		return self::$instance;

	}
}