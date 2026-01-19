<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\economy;

use pocketmine\player\Player;

/**
 * Economy gateway.
 */
interface EconomyGateway {

	/**
	 * Returns money.
	 *
	 * @param Player|string $player Player instance or name.
	 *
	 * @return float
	 */
	public function getMoney(Player|string $player) :float;

	/**
	 * Reduces money.
	 *
	 * @param Player|string $player Player instance or name.
	 * @param float $amount Amount.
	 *
	 * @return bool True on success.
	 */
	public function reduceMoney(Player|string $player, float $amount) :bool;
}
