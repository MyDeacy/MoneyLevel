<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\application;

use net\mydeacy\moneylevel\domain\LevelEntry;
use net\mydeacy\moneylevel\domain\LevelRepository;

/**
 * Level service.
 */
final class LevelService {

	private LevelRepository $repository;

	private int $initialLevel;

	/**
	 * Creates a new instance.
	 *
	 * @param LevelRepository $repository Repository.
	 * @param int $initialLevel Initial level.
	 */
	public function __construct(LevelRepository $repository, int $initialLevel) {
		$this->repository = $repository;
		$this->initialLevel = max(1, $initialLevel);
	}

	/**
	 * Ensures a level entry exists.
	 *
	 * @param string $playerName Player name.
	 * @param string $displayName Display name.
	 *
	 * @return LevelEntry
	 */
	public function ensure(string $playerName, string $displayName) :LevelEntry {
		$normalized = LevelEntry::normalizeName($playerName);
		$entry = $this->repository->findByName($normalized);
		if ($entry === null) {
			$entry = new LevelEntry($normalized, $displayName, $this->initialLevel);
			$this->repository->save($entry);
			return $entry;
		}
		if ($entry->getDisplayName() !== $displayName) {
			$entry = $entry->withDisplayName($displayName);
			$this->repository->save($entry);
		}
		return $entry;
	}

	/**
	 * Finds a level entry by player name.
	 *
	 * @param string $playerName Player name.
	 *
	 * @return ?LevelEntry Value or null if not available.
	 */
	public function find(string $playerName) :?LevelEntry {
		return $this->repository->findByName(LevelEntry::normalizeName($playerName));
	}

	/**
	 * Sets level.
	 *
	 * @param string $playerName Player name.
	 * @param string $displayName Display name.
	 * @param int $level Level.
	 *
	 * @return LevelEntry
	 */
	public function setLevel(string $playerName, string $displayName, int $level) :LevelEntry {
		$normalized = LevelEntry::normalizeName($playerName);
		$level = max(1, $level);
		$entry = $this->repository->findByName($normalized);
		if ($entry === null) {
			$entry = new LevelEntry($normalized, $displayName, $level);
		} else {
			$entry = $entry->withLevel($level)->withDisplayName($displayName);
		}
		$this->repository->save($entry);
		return $entry;
	}

	/**
	 * Returns all level entries.
	 *
	 * @return LevelEntry[]
	 */
	public function listAll() :array {
		return $this->repository->listAll();
	}
}
