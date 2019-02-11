<?php

namespace mydeacy\moneylevel;

use mydeacy\moneylevel\events\MoneyLevelUpEvent;
use mydeacy\moneylevel\service\MoneyLevelAPI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Server;

class EventListener implements Listener {

	private $api;

	public function __construct(MoneyLevelAPI $api){
		$this->api = $api;
	}

	/**
	 * @param \pocketmine\event\player\PlayerJoinEvent $event
	 *
	 * @priority HIGHEST
	 */
	function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$api = $this->api;
		if(empty($api->getLv($name))){
			$api->registerUser($name);
		}
		$level = $api->getLv($name);
		$api->setLvTag($player, $level);
	}

	function onLevelUp(MoneyLevelUpEvent $event){
		$name = $event->getUser();
		if(!empty($player = Server::getInstance()->getPlayer($name))){
			$this->api->setLvTag($player, $event->getLv(), $event->getBeforeLv());
		}
	}
}