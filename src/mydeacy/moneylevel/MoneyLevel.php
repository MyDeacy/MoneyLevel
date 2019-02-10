<?php

namespace mydeacy\moneylevel;

use mydeacy\moneylevel\utils\SQLiteDB;
use mydeacy\moneylevel\service\MoneyLevelAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class MoneyLevel extends PluginBase {

	/**@var \mydeacy\moneylevel\service\MoneyLevelAPI */
	private $api;

	/**@var \mydeacy\moneylevel\CommandProcessor */
	private $processor;

	public function onLoad(){
		$this->api = new MoneyLevelAPI($this);
	}

	public function onEnable(){
		$this->getServer()->getPluginManager()
			->registerEvents(new EventListener($this->api), $this);
		$this->processor = new CommandProcessor($this->api);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		return $this->processor->onCommand($sender, $label, $args);
	}
}