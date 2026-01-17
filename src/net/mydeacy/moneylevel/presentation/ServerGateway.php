<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\presentation;

use pocketmine\player\Player;

interface ServerGateway {
    public function broadcastMessage(string $message): void;

    public function getPlayerExact(string $name): ?Player;

    /**
     * @return array<string, true>
     */
    public function getOpNameMap(): array;
}
