<?php

namespace mydeacy\moneylevel\service;

use mydeacy\moneylevel\events\MoneyLevelChangeEvent;
use mydeacy\moneylevel\events\MoneyLevelUpEvent;
use mydeacy\moneylevel\MoneyLevel;
use mydeacy\moneylevel\utils\Language;
use mydeacy\moneylevel\utils\SQLiteDB;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class MoneyLevelAPI implements APIService {

	const Prefix = "§r§f[§aML§f] ";

	private $main, $db, $config, $language;

	private static $instance;

	public function __construct(MoneyLevel $main){
		$this->main = $main;
		$dir = $main->getDataFolder();
		$this->db = new SQLiteDB($dir);
		$this->config = new Config($dir."config.yml", Config::YAML, [
			'up-required-money' => 1000,
			'toplv-enable-op'   => false,
			'broadcast-notice'  => true,
			'lang'              => "jpn"
		]);
		$this->language = new Language($this->config->get("lang"));
		self::$instance = $this;
	}

	/**
	 * @return \mydeacy\moneylevel\service\MoneyLevelAPI
	 */
	public static function getInstance(): self{
		return self::$instance;
	}

	public function registerUser(String $playerName): void{
		$this->db->registerUser($playerName);
	}

	public function getLv(String $playerName): ?int{
		return $this->db->getLv($playerName);
	}

	public function setLv(String $playerName, int $level): bool{
		return $this->db->setLv($playerName, $level);
	}

	public function lvUp(String $playerName, int $amount): bool{
		$before = $this->db->getLv($playerName);
		if(empty($before))
			return false;
		$level = $before + abs($amount);
		($event = new MoneyLevelUpEvent($this->main, $playerName, $level, $before))->call();
		if(!$event->isCancelled())
			$this->setLv($playerName, $level);
		return true;
	}

	public function getDefName(String $playerName): String{
		return $this->db->getDefName($playerName);
	}

	public function getAll(): array{
		return $this->db->getAllData();
	}

	public function getLanguage(): Language{
		return $this->language;
	}

	public function getConfig(): Config{
		return $this->config;
	}

	public function setLvTag(Player $player, int $level, ?int $before = null): void{
		if(empty($before))
			$before = $level;
		$tag = $this->language->getReplacedText("player.tag", [$level], false);
		$beforeTag = $this->language->getReplacedText("player.tag", [$before], false);
		$nameTag = str_replace($beforeTag, "", $player->getNameTag());
		$displayTag = str_replace($beforeTag, "", $player->getDisplayName());
		$player->setNameTag($tag.$nameTag);
		$player->setDisplayName($tag.$displayTag);
	}

	public function noticeAll(Player $player, int $level): void{
		Server::getInstance()
			->broadcastTip(($this->language->getReplacedText("command.lvup.success.broadcast",
				[$player->getName(), $level], false)));
	}
}