<?php


namespace RuinPray\MoneyLevel\Task;

use pocketmine\scheduler\PluginTask;
use RuinPray\MoneyLevel\Main;

class SetTagTask extends PluginTask {

	public function __construct(Main $main, $lv, $player){
		parent::__construct($main);
		$this->lv = $lv;
		$this->player = $player;
	}

	public function onRun(int $tick){
		$main = $this->getOwner();
		$main->setLvTag($this->player, $this->lv);
	}
}