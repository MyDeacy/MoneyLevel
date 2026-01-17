<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\presentation;

use net\mydeacy\moneylevel\infrastructure\text\MessageCatalog;
use pocketmine\player\Player;

final class NameTagService {
    private MessageCatalog $messages;

    public function __construct(MessageCatalog $messages) {
        $this->messages = $messages;
    }

    public function apply(Player $player, int $level): void {
        $tag = $this->messages->raw("player.tag", [$level]);
        if ($tag !== "") {
            $player->setNameTag($tag);
        }
    }
}
