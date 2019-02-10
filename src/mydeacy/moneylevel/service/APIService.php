<?php

namespace mydeacy\moneylevel\service;

use mydeacy\moneylevel\utils\Language;
use pocketmine\Player;
use pocketmine\utils\Config;

interface APIService {

	/**
	 * @param String $playerName
	 */
	public function registerUser(String $playerName): void;

	/**
	 * @param String $playerName
	 *
	 * @return int|null
	 */
	public function getLv(String $playerName): ?int;

	/**
	 * @param String $playerName
	 * @param int    $level
	 *
	 * @return bool
	 */
	public function setLv(String $playerName, int $level): bool;

	/**
	 * @param String $playerName
	 * @param int    $amount
	 *
	 * @return bool
	 */
	public function lvUp(String $playerName, int $amount): bool;

	/**
	 * @param String $playerName
	 *
	 * @return String
	 */
	public function getDefName(String $playerName): String;

	/**
	 * @return array
	 */
	public function getAll(): array;

	/**
	 * @return \mydeacy\moneylevel\utils\Language
	 */
	public function getLanguage(): Language;

	/**
	 * @return \pocketmine\utils\Config
	 */
	public function getConfig(): Config;


	/**
	 * @param \pocketmine\Player $player
	 * @param int                $level
	 * @param int                $before
	 */
	public function setLvTag(Player $player, int $level, int $before): void;
}