<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\domain;

final class LevelEntry {
    private string $name;
    private string $displayName;
    private int $level;

    public function __construct(string $name, string $displayName, int $level) {
        $this->name = self::normalizeName($name);
        $this->displayName = $displayName;
        $this->level = max(1, $level);
    }

    public static function normalizeName(string $name): string {
        return strtolower($name);
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDisplayName(): string {
        return $this->displayName;
    }

    public function getLevel(): int {
        return $this->level;
    }

    public function withLevel(int $level): self {
        return new self($this->name, $this->displayName, $level);
    }

    public function withDisplayName(string $displayName): self {
        return new self($this->name, $displayName, $this->level);
    }
}
