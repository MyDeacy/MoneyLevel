<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

/**
 * Fired when a player's level is about to change.
 */
final class LevelUpEvent extends Event implements Cancellable {

	use CancellableTrait;

	private string $playerName;

	private int $beforeLevel;

	private int $afterLevel;

	/**
	 * Creates a new instance.
	 *
	 * @param string $playerName Player name.
	 * @param int $beforeLevel Before level.
	 * @param int $afterLevel After level.
	 */
	public function __construct(string $playerName, int $beforeLevel, int $afterLevel) {
		$this->playerName = $playerName;
		$this->beforeLevel = $beforeLevel;
		$this->afterLevel = max(1, $afterLevel);
	}

	/**
	 * Returns player name.
	 *
	 * @return string
	 */
	public function getPlayerName() :string {
		return $this->playerName;
	}

	/**
	 * Returns before level.
	 *
	 * @return int
	 */
	public function getBeforeLevel() :int {
		return $this->beforeLevel;
	}

	/**
	 * Returns after level.
	 *
	 * @return int
	 */
	public function getAfterLevel() :int {
		return $this->afterLevel;
	}

	/**
	 * Sets after level.
	 *
	 * @param int $afterLevel After level.
	 */
	public function setAfterLevel(int $afterLevel) :void {
		$this->afterLevel = max(1, $afterLevel);
	}
}
