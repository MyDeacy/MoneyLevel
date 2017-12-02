<?php

namespace RuinPray\MoneyLevel\Commands;

use pocketmine\command\{PluginCommand, Command,CommandSender,CommandExecutor};
use RuinPray\MoneyLevel\Main;
use pocketmine\Player;

class TakelvCommand extends PluginCommand implements CommandExecutor {

	public function __construct(Main $main){
		parent::__construct('takelv', $main);
		$this->setPermission('molcmdop');
		$this->setDescription("プレイヤーのレベルを増やします。");
		$this->setUsage("/takelv <name> <amount>");
		$this->setExecutor($this);
	}


	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$main = $this->getPlugin();
		if(!isset($args[1])){
			$sender->sendMessage($main->getText("command.admin.error.args"));
			return false;
		}
		if(($lv = $main->getLv($args[0])) !== false){
			$value = $lv - (int)$args[1];
			if($value < 0){
				$value = 1;
				$sender->sendMessage($main->getText("command.takelv.overmin"));
			}
			$bool = false;
			if($main->getServer()->getPlayer($args[0]) !== null){
				$bool = true;
			}
			$main->setLv($args[0], $value, $bool);
			$sender->sendMessage($main->getReplacedText("command.takelv.success", [$args[0], (int)$args[1], $value]));
		}else{
			$sender->sendMessage($main->getReplacedText("error.notfound", [$args[0]]));
		}
		return true;
	}
}