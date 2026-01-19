<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\presentation;

use net\mydeacy\moneylevel\infrastructure\text\MessageCatalog;
use pocketmine\player\Player;

/**
 * Name tag service.
 */
final class NameTagService {

	private MessageCatalog $messages;

	/**
	 * Creates a new instance.
	 *
	 * @param MessageCatalog $messages Messages.
	 */
	public function __construct(MessageCatalog $messages) {
		$this->messages = $messages;
	}

	/**
	 * Applies the level name tag.
	 *
	 * @param Player $player Player instance.
	 * @param int $level Level.
	 */
	public function apply(Player $player, int $level) :void {
		$tag = $this->messages->raw("player.tag", [$level]);
		if ($tag !== "") {
			$player->setNameTag($tag);
		}
	}
}
