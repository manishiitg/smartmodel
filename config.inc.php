<?php

// GOOGLE MAPS API KEY
// ABQIAAAAViv8xovSrRDZZ6HVw2UCFBRudQU30vYgZd0u2xHUlS4zJH-vOxSyzID2XBowNTl86_IS2ViiwzkoGQ

class Configuration {
	/*
	 * MySQL Datebase Settings
	 */
	const host = 'localhost';
	const db = 'redit-cms';
	//const user = "root";
	const user = "root";
	//const pass = "";
	const pass = "root";
	const sql_tracking = false;
	const debug = false;

	public static function db(){
		$link = mysql_connect(Configuration::host,Configuration::user,Configuration::pass);
		if(!$link)
		die(mysql_error());
		mysql_select_db(Configuration::db,$link) || die(mysql_error($link));
		return $link;
	}

	public static function close($link){
		mysql_close($link);
	}

}
?>
