<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

final class LevelUpEvent extends Event implements Cancellable {
    use CancellableTrait;
    private string $playerName;
    private int $beforeLevel;
    private int $afterLevel;

    public function __construct(string $playerName, int $beforeLevel, int $afterLevel) {
        $this->playerName = $playerName;
        $this->beforeLevel = $beforeLevel;
        $this->afterLevel = max(1, $afterLevel);
    }

    public function getPlayerName(): string {
        return $this->playerName;
    }

    public function getBeforeLevel(): int {
        return $this->beforeLevel;
    }

    public function getAfterLevel(): int {
        return $this->afterLevel;
    }

    public function setAfterLevel(int $afterLevel): void {
        $this->afterLevel = max(1, $afterLevel);
    }
}
