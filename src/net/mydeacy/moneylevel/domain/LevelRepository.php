<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\domain;

interface LevelRepository {
    public function findByName(string $name): ?LevelEntry;

    public function save(LevelEntry $entry): void;

    /**
     * @return LevelEntry[]
     */
    public function listAll(): array;
}
