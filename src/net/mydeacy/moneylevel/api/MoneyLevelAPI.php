<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\api;

use net\mydeacy\moneylevel\application\LevelService;
use net\mydeacy\moneylevel\domain\LevelEntry;

/**
 * Public API for MoneyLevel.
 */
final class MoneyLevelAPI {

	private LevelService $service;

	/**
	 * Creates a new instance.
	 *
	 * @param LevelService $service Service.
	 */
	public function __construct(LevelService $service) {
		$this->service = $service;
	}

	/**
	 * Adds to the player's level and returns the new value.
	 */
	public function addLevel(string $playerName, int $amount, ?string $displayName = null) :int {
		$displayName ??= $playerName;
		$entry = $this->service->ensure($playerName, $displayName);
		return $this->service->setLevel($playerName, $displayName, $entry->getLevel() + $amount)->getLevel();
	}

	/**
	 * Ensures the player has an entry and returns the level.
	 */
	public function ensure(string $playerName, ?string $displayName = null) :int {
		$displayName ??= $playerName;
		return $this->service->ensure($playerName, $displayName)->getLevel();
	}

	/**
	 * Returns the current level for a player.
	 */
	public function getLevel(string $playerName) :?int {
		$entry = $this->service->find($playerName);
		return $entry?->getLevel();
	}

	/**
	 * Sets the player's level and returns the new value.
	 */
	public function setLevel(string $playerName, int $level, ?string $displayName = null) :int {
		$displayName ??= $playerName;
		return $this->service->setLevel($playerName, $displayName, $level)->getLevel();
	}

	/**
	 * Returns all level entries.
	 *
	 * @return LevelEntry[]
	 */
	public function listAll() :array {
		return $this->service->listAll();
	}
}
