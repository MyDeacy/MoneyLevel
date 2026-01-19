<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\server;

use net\mydeacy\moneylevel\presentation\ServerGateway;
use pocketmine\player\Player;
use pocketmine\Server;
use function strtolower;

/**
 * PMMP server gateway.
 */
final class PmmpServerGateway implements ServerGateway {

	private Server $server;

	/**
	 * Creates a new instance.
	 *
	 * @param Server $server Server.
	 */
	public function __construct(Server $server) {
		$this->server = $server;
	}

	/**
	 * Broadcasts a message.
	 *
	 * @param string $message Message.
	 */
	public function broadcastMessage(string $message) :void {
		$this->server->broadcastMessage($message);
	}

	/**
	 * Returns a player by exact name.
	 *
	 * @param string $name Name.
	 *
	 * @return ?Player Value or null if not available.
	 */
	public function getPlayerExact(string $name) :?Player {
		return $this->server->getPlayerExact($name);
	}

	/**
	 * Returns op names as a lookup map.
	 *
	 * @return array<string, true>
	 */
	public function getOpNameMap() :array {
		$map = [];
		foreach ($this->server->getOps()->getAll() as $name => $value) {
			$map[strtolower((string)$name)] = true;
		}
		return $map;
	}
}
