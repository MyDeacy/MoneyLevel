<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\presentation;

use pocketmine\player\Player;

/**
 * Server gateway.
 */
interface ServerGateway {

	/**
	 * Broadcasts a message.
	 *
	 * @param string $message Message.
	 */
	public function broadcastMessage(string $message) :void;

	/**
	 * Returns a player by exact name.
	 *
	 * @param string $name Name.
	 *
	 * @return ?Player Value or null if not available.
	 */
	public function getPlayerExact(string $name) :?Player;

	/**
	 * Returns op names as a lookup map.
	 *
	 * @return array<string, true>
	 */
	public function getOpNameMap() :array;
}
