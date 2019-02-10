<?php

namespace mydeacy\moneylevel\utils;

class SQLiteDB {

	private $db;

	public function __construct(String $dir){
		$this->db = new \SQLite3($dir."user.sqlite");
		$this->db->exec("CREATE TABLE IF NOT EXISTS levelData (
			name TEXT NOT NULL PRIMARY KEY,
			defName TEXT NOT NULL,
			level INTEGER NOT NULL
		)");
	}

	public function registerUser(String $name): void{
		$db = $this->db->prepare(
			"INSERT INTO levelData (name, defName, level) VALUES (:name, :defName, :level)");
		$names = strtolower($name);
		$db->bindValue(":name", $names, SQLITE3_TEXT);
		$db->bindValue(":defName", $name, SQLITE3_TEXT);
		$db->bindValue(":level", 1, SQLITE3_INTEGER);
		$db->execute();
	}

	public function setLv(String $name, int $lv): bool{
		if(!empty($this->getLv($name))){
			$name = strtolower($name);
			$db = $this->db->prepare("UPDATE levelData SET level = :level WHERE name = :name");
			$db->bindValue(":level", $lv, SQLITE3_INTEGER);
			$db->bindValue(":name", $name, SQLITE3_TEXT);
			$db->execute();
			return true;
		}
		return false;
	}

	public function getLv(String $name): ?int{
		$name = strtolower($name);
		$db = $this->db->prepare("SELECT level FROM levelData WHERE name = :name");
		$db->bindValue(":name", $name, SQLITE3_TEXT);
		$result = $db->execute()->fetchArray(SQLITE3_ASSOC);
		return empty($result) ? null : $result["level"];
	}

	public function getDefName(String $name): String{
		$db = $this->db->prepare("SELECT defName FROM levelData WHERE name = :name");
		$db->bindValue(":name", strtolower($name), SQLITE3_TEXT);
		$result = $db->execute()->fetchArray(SQLITE3_ASSOC);
		return empty($result) ? "" : $result["defName"];
	}

	public function getAllData(): array{
		$db = $this->db->prepare("SELECT * FROM levelData");
		$result = $db->execute();
		$data = [];
		while($d = $result->fetchArray(SQLITE3_ASSOC)){
			$data[] = $d;
		}
		return $data;
	}
}