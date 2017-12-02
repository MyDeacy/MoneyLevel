<?php

namespace RuinPray\MoneyLevel;

use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};
use RuinPray\MoneyLevel\Main;
use RuinPray\MoneyLevel\Task\SetTagTask;

class EventListener implements Listener {

	public function __construct(Main $main){
		$this->m = $main;
	}

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		$main = $this->m;
		if($main->getLv($name) === false){
			$main->registerUser($name);
		}
		$lv = $main->getLv($name);
		$task = new SetTagTask($main, $lv, $player);
		$main->getServer()->getScheduler()->scheduleDelayedTask($task, 10);//競合防止のため遅延
	}

	public function onQuit(PlayerQuitEvent $event){
		$player = $event->getPlayer();
		$main = $this->m;
		unset($main->tag[$player->getName()]);
	}
}