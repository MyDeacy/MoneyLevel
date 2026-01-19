<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\domain;

/**
 * Immutable level record for a player.
 */
final class LevelEntry {

	private string $name;

	private string $displayName;

	private int $level;

	/**
	 * Creates a new instance.
	 *
	 * @param string $name Name.
	 * @param string $displayName Display name.
	 * @param int $level Level.
	 */
	public function __construct(string $name, string $displayName, int $level) {
		$this->name = self::normalizeName($name);
		$this->displayName = $displayName;
		$this->level = max(1, $level);
	}

	/**
	 * Normalizes a player name.
	 *
	 * @param string $name Name.
	 *
	 * @return string
	 */
	public static function normalizeName(string $name) :string {
		return strtolower($name);
	}

	/**
	 * Returns name.
	 *
	 * @return string
	 */
	public function getName() :string {
		return $this->name;
	}

	/**
	 * Returns display name.
	 *
	 * @return string
	 */
	public function getDisplayName() :string {
		return $this->displayName;
	}

	/**
	 * Returns level.
	 *
	 * @return int
	 */
	public function getLevel() :int {
		return $this->level;
	}

	/**
	 * Returns a new instance with updated level.
	 *
	 * @param int $level Level.
	 *
	 * @return self
	 */
	public function withLevel(int $level) :self {
		return new self($this->name, $this->displayName, $level);
	}

	/**
	 * Returns a new instance with updated display name.
	 *
	 * @param string $displayName Display name.
	 *
	 * @return self
	 */
	public function withDisplayName(string $displayName) :self {
		return new self($this->name, $displayName, $this->level);
	}
}
