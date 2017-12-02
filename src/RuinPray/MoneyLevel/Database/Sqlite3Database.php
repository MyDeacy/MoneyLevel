<?php
namespace RuinPray\MoneyLevel\Database;
use RuinPray\MoneyLevel\Main;

class Sqlite3Database {

	private $db;

	public function __construct(string $dir, Main $main){
		$this->db = new \SQLite3($dir."user.sqlite");
		$this->db->exec("CREATE TABLE IF NOT EXISTS lvdata (
			name TEXT NOT NULL PRIMARY KEY,
			defname TEXT NOT NULL,
			level INTEGER NOT NULL
		)");
	}


	public function registerUser(string $name){
		$value = "INSERT INTO lvdata (name, defname, level) VALUES (:name, :defname, :level)";
		$db = $this->db->prepare($value);
		if(($names = strtolower($name)) === $name){ 
			$name = "-";
		}
		$db->bindValue(":name", $names, SQLITE3_TEXT);
		$db->bindValue(":defname", $name, SQLITE3_TEXT);
		$db->bindValue(":level", 1, SQLITE3_TEXT);
		$db->execute();
	}


	public function setLv(string $name, int $lv){
		$value = "UPDATE lvdata SET level = :level WHERE name = :name";
		$db = $this->db->prepare($value);
		$db->bindValue(":level", $lv, SQLITE3_TEXT);
		$db->bindValue(":name", $name, SQLITE3_TEXT);
		$db->execute();
	}


	public function getLv(string $name){
		$value = "SELECT level FROM lvdata WHERE name = :name";
		$db = $this->db->prepare($value);
		$db->bindValue(":name", $name, SQLITE3_TEXT);
		$result = $db->execute()->fetchArray(SQLITE3_ASSOC);
		if(empty($result)){
			return false;
		}
		return $result["level"];
	}

	public function getDefName(string $name){
		$value = "SELECT defname FROM lvdata WHERE name = :name";
		$db = $this->db->prepare($value);
		$db->bindValue(":name", $name, SQLITE3_TEXT);
		$result = $db->execute()->fetchArray(SQLITE3_ASSOC);
		if($result["defname"] === "-"){
			$result["defname"] = $result["name"];
		}
		return $result["defname"];
	}

	public function getAllData(): array{
		$value = "SELECT * FROM lvdata";
		$db = $this->db->prepare($value);
		$result = $db->execute();
		$data = [];
		while($d = $result->fetchArray(SQLITE3_ASSOC)){
			$data[] = $d;
		}
		return $data;
	}
}
