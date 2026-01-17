<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\application;

use net\mydeacy\moneylevel\domain\LevelEntry;
use net\mydeacy\moneylevel\domain\LevelRepository;

final class LevelService {
    private LevelRepository $repository;
    private int $initialLevel;

    public function __construct(LevelRepository $repository, int $initialLevel) {
        $this->repository = $repository;
        $this->initialLevel = max(1, $initialLevel);
    }

    public function ensure(string $playerName, string $displayName): LevelEntry {
        $normalized = LevelEntry::normalizeName($playerName);
        $entry = $this->repository->findByName($normalized);

        if ($entry === null) {
            $entry = new LevelEntry($normalized, $displayName, $this->initialLevel);
            $this->repository->save($entry);
            return $entry;
        }

        if ($entry->getDisplayName() !== $displayName) {
            $entry = $entry->withDisplayName($displayName);
            $this->repository->save($entry);
        }

        return $entry;
    }

    public function find(string $playerName): ?LevelEntry {
        return $this->repository->findByName(LevelEntry::normalizeName($playerName));
    }

    public function setLevel(string $playerName, string $displayName, int $level): LevelEntry {
        $normalized = LevelEntry::normalizeName($playerName);
        $level = max(1, $level);
        $entry = $this->repository->findByName($normalized);

        if ($entry === null) {
            $entry = new LevelEntry($normalized, $displayName, $level);
        } else {
            $entry = $entry->withLevel($level)->withDisplayName($displayName);
        }

        $this->repository->save($entry);
        return $entry;
    }

    /**
     * @return LevelEntry[]
     */
    public function listAll(): array {
        return $this->repository->listAll();
    }
}
