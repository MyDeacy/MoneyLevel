<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\server;

use net\mydeacy\moneylevel\presentation\ServerGateway;
use pocketmine\player\Player;
use pocketmine\Server;
use function strtolower;

final class PmmpServerGateway implements ServerGateway {
    private Server $server;

    public function __construct(Server $server) {
        $this->server = $server;
    }

    public function broadcastMessage(string $message): void {
        $this->server->broadcastMessage($message);
    }

    public function getPlayerExact(string $name): ?Player {
        return $this->server->getPlayerExact($name);
    }

    public function getOpNameMap(): array {
        $map = [];
        foreach ($this->server->getOps()->getAll() as $name => $value) {
            $map[strtolower((string) $name)] = true;
        }
        return $map;
    }
}
