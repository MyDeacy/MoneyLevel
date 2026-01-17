<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\api;

use net\mydeacy\moneylevel\application\LevelService;
use net\mydeacy\moneylevel\domain\LevelEntry;

final class MoneyLevelAPI {
    private LevelService $service;

    public function __construct(LevelService $service) {
        $this->service = $service;
    }

    public function getLevel(string $playerName): ?int {
        $entry = $this->service->find($playerName);
        return $entry?->getLevel();
    }

    public function ensure(string $playerName, ?string $displayName = null): int {
        $displayName ??= $playerName;
        return $this->service->ensure($playerName, $displayName)->getLevel();
    }

    public function setLevel(string $playerName, int $level, ?string $displayName = null): int {
        $displayName ??= $playerName;
        return $this->service->setLevel($playerName, $displayName, $level)->getLevel();
    }

    public function addLevel(string $playerName, int $amount, ?string $displayName = null): int {
        $displayName ??= $playerName;
        $entry = $this->service->ensure($playerName, $displayName);
        return $this->service->setLevel($playerName, $displayName, $entry->getLevel() + $amount)->getLevel();
    }

    /**
     * @return LevelEntry[]
     */
    public function listAll(): array {
        return $this->service->listAll();
    }
}
