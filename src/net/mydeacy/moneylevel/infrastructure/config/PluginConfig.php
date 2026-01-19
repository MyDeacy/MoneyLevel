<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\config;

use pocketmine\utils\Config;

/**
 * Plugin configuration.
 */
final class PluginConfig {

	private int $initialLevel;

	private int $requiredMoneyPerLevel;

	private bool $broadcastNotice;

	private bool $topIncludeOp;

	private string $language;

	private int $rankingPageSize;

	/**
	 * Creates a new instance.
	 *
	 * @param Config $config Config.
	 */
	public function __construct(Config $config) {
		$this->initialLevel = max(1, (int)$config->get("initial-level", 1));
		$this->requiredMoneyPerLevel = max(0, (int)$config->get("up-required-money", 1000));
		$this->broadcastNotice = (bool)$config->get("broadcast-notice", true);
		$this->topIncludeOp = (bool)$config->get("toplv-enable-op", true);
		$this->language = strtolower((string)$config->get("language", "jpn"));
		$this->rankingPageSize = max(1, (int)$config->get("toplv-per-page", 5));
	}

	/**
	 * Returns initial level.
	 *
	 * @return int
	 */
	public function getInitialLevel() :int {
		return $this->initialLevel;
	}

	/**
	 * Returns required money per level.
	 *
	 * @return int
	 */
	public function getRequiredMoneyPerLevel() :int {
		return $this->requiredMoneyPerLevel;
	}

	/**
	 * Returns whether level-up notices are broadcast.
	 *
	 * @return bool True on success.
	 */
	public function isBroadcastNotice() :bool {
		return $this->broadcastNotice;
	}

	/**
	 * Returns whether ops appear in rankings.
	 *
	 * @return bool True on success.
	 */
	public function isTopIncludeOp() :bool {
		return $this->topIncludeOp;
	}

	/**
	 * Returns the language code.
	 *
	 * @return string
	 */
	public function getLanguage() :string {
		return $this->language;
	}

	/**
	 * Returns ranking page size.
	 *
	 * @return int
	 */
	public function getRankingPageSize() :int {
		return $this->rankingPageSize;
	}
}
