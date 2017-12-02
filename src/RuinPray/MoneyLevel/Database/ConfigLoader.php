<?php

namespace RuinPray\MoneyLevel\Database;

use pocketmine\utils\Config;
use pocketmine\Plugin\PluginBase;
use RuinPray\MoneyLevel\Main;

class ConfigLoader extends PluginBase {

	public function __construct(Main $main){
		$this->m = $main;

		if(!file_exists($this->m->getDataFolder())){
			mkdir($this->m->getDataFolder(), 0744, true);
		}
		$this->c = new Config($this->m->getDataFolder() . "config.yml", Config::YAML, [
			'up-required-money' => 1000,
			'toplv-enable-op' => false,
			'broadcast-notice' => true
		]);
	}

	public function getSettings(): array{
		return $this->c->getAll();
	}

}