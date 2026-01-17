<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\economy;

use pocketmine\player\Player;

interface EconomyGateway {
    public function getMoney(Player|string $player): float;

    public function reduceMoney(Player|string $player, float $amount): bool;
}
