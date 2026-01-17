<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\config;

use pocketmine\utils\Config;

final class PluginConfig {
    private int $initialLevel;
    private int $requiredMoneyPerLevel;
    private bool $broadcastNotice;
    private bool $topIncludeOp;
    private string $language;
    private int $rankingPageSize;

    public function __construct(Config $config) {
        $this->initialLevel = max(1, (int) $config->get("initial-level", 1));
        $this->requiredMoneyPerLevel = max(0, (int) $config->get("up-required-money", 1000));
        $this->broadcastNotice = (bool) $config->get("broadcast-notice", true);
        $this->topIncludeOp = (bool) $config->get("toplv-enable-op", true);
        $this->language = strtolower((string) $config->get("language", "jpn"));
        $this->rankingPageSize = max(1, (int) $config->get("toplv-per-page", 5));
    }

    public function getInitialLevel(): int {
        return $this->initialLevel;
    }

    public function getRequiredMoneyPerLevel(): int {
        return $this->requiredMoneyPerLevel;
    }

    public function isBroadcastNotice(): bool {
        return $this->broadcastNotice;
    }

    public function isTopIncludeOp(): bool {
        return $this->topIncludeOp;
    }

    public function getLanguage(): string {
        return $this->language;
    }

    public function getRankingPageSize(): int {
        return $this->rankingPageSize;
    }
}
