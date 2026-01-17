<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\economy;

use onebone\economyapi\EconomyAPI;
use pocketmine\player\Player;
use pocketmine\Server;
use RuntimeException;

final class EconomyApiGateway implements EconomyGateway {
    private EconomyAPI $economy;

    public function __construct(Server $server) {
        $plugin = $server->getPluginManager()->getPlugin("EconomyAPI");
        if (!$plugin instanceof EconomyAPI || !$plugin->isEnabled()) {
            throw new RuntimeException("EconomyAPI is not enabled.");
        }
        $this->economy = EconomyAPI::getInstance();
    }

    public function getMoney(Player|string $player): float {
        $money = $this->economy->myMoney($player);
        return is_numeric($money) ? (float) $money : 0.0;
    }

    public function reduceMoney(Player|string $player, float $amount): bool {
        return $this->economy->reduceMoney($player, $amount) === EconomyAPI::RET_SUCCESS;
    }
}
