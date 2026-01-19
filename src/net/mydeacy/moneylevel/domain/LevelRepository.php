<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\domain;

/**
 * Level repository interface.
 */
interface LevelRepository {

	/**
	 * Finds an entry by name.
	 *
	 * @param string $name Name.
	 *
	 * @return ?LevelEntry Value or null if not available.
	 */
	public function findByName(string $name) :?LevelEntry;

	/**
	 * Saves a level entry.
	 *
	 * @param LevelEntry $entry Entry.
	 */
	public function save(LevelEntry $entry) :void;

	/**
	 * Returns all level entries.
	 *
	 * @return LevelEntry[]
	 */
	public function listAll() :array;
}
