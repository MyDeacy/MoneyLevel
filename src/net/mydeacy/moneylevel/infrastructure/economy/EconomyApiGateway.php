<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\economy;

use onebone\economyapi\EconomyAPI;
use pocketmine\player\Player;
use pocketmine\Server;
use RuntimeException;

/**
 * Economy API gateway.
 */
final class EconomyApiGateway implements EconomyGateway {

	private EconomyAPI $economy;

	/**
	 * Creates a new instance.
	 *
	 * @param Server $server Server.
	 */
	public function __construct(Server $server) {
		$plugin = $server->getPluginManager()->getPlugin("EconomyAPI");
		if (!$plugin instanceof EconomyAPI || !$plugin->isEnabled()) {
			throw new RuntimeException("EconomyAPI is not enabled.");
		}
		$this->economy = EconomyAPI::getInstance();
	}

	/**
	 * Returns money.
	 *
	 * @param Player|string $player Player instance or name.
	 *
	 * @return float
	 */
	public function getMoney(Player|string $player) :float {
		$money = $this->economy->myMoney($player);
		return is_numeric($money) ? (float)$money : 0.0;
	}

	/**
	 * Reduces money.
	 *
	 * @param Player|string $player Player instance or name.
	 * @param float $amount Amount.
	 *
	 * @return bool True on success.
	 */
	public function reduceMoney(Player|string $player, float $amount) :bool {
		return $this->economy->reduceMoney($player, $amount) === EconomyAPI::RET_SUCCESS;
	}
}
