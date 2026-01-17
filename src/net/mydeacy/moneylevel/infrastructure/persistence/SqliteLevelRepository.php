<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\persistence;

use net\mydeacy\moneylevel\domain\LevelEntry;
use net\mydeacy\moneylevel\domain\LevelRepository;
use SQLite3;
use SQLite3Stmt;
use function is_dir;
use function mkdir;

final class SqliteLevelRepository implements LevelRepository {
    private SQLite3 $db;
    private SQLite3Stmt $findStmt;
    private SQLite3Stmt $saveStmt;

    public function __construct(string $path) {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $this->db = new SQLite3($path, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $this->db->busyTimeout(2000);
        $this->db->exec("PRAGMA journal_mode=WAL;");
        $this->db->exec("CREATE TABLE IF NOT EXISTS levels (name TEXT PRIMARY KEY, display_name TEXT NOT NULL, level INTEGER NOT NULL);");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_levels_level ON levels(level);");

        $this->findStmt = $this->db->prepare("SELECT name, display_name, level FROM levels WHERE name = :name LIMIT 1;");
        $this->saveStmt = $this->db->prepare("INSERT OR REPLACE INTO levels (name, display_name, level) VALUES (:name, :display_name, :level);");
    }

    public function findByName(string $name): ?LevelEntry {
        $this->findStmt->reset();
        $this->findStmt->bindValue(":name", $name, SQLITE3_TEXT);
        $result = $this->findStmt->execute();
        if ($result === false) {
            return null;
        }
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if (!is_array($row)) {
            return null;
        }
        return new LevelEntry((string) $row["name"], (string) $row["display_name"], (int) $row["level"]);
    }

    public function save(LevelEntry $entry): void {
        $this->saveStmt->reset();
        $this->saveStmt->bindValue(":name", $entry->getName(), SQLITE3_TEXT);
        $this->saveStmt->bindValue(":display_name", $entry->getDisplayName(), SQLITE3_TEXT);
        $this->saveStmt->bindValue(":level", $entry->getLevel(), SQLITE3_INTEGER);
        $this->saveStmt->execute();
    }

    /**
     * @return LevelEntry[]
     */
    public function listAll(): array {
        $result = $this->db->query("SELECT name, display_name, level FROM levels;");
        if ($result === false) {
            return [];
        }
        $entries = [];
        while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== false) {
            $entries[] = new LevelEntry((string) $row["name"], (string) $row["display_name"], (int) $row["level"]);
        }
        return $entries;
    }

    public function __destruct() {
        $this->db->close();
    }
}
