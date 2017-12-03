<?php

namespace RuinPray\MoneyLevel\Commands;

use pocketmine\command\{PluginCommand, Command,CommandSender,CommandExecutor};
use RuinPray\MoneyLevel\Main;
use pocketmine\Player;

class AddlvCommand extends PluginCommand implements CommandExecutor {

	public function __construct(Main $main){
		parent::__construct('addlv', $main);
		$this->setPermission('molcmdop');
		$this->setDescription("プレイヤーのレベルを増やします。");
		$this->setUsage("/addlv <name> <amount>");
		$this->setExecutor($this);
	}


	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$main = $this->getPlugin();
		if(!isset($args[1])){
			$sender->sendMessage($main->getText("command.admin.error.args"));
			return false;
		}
		if(($lv = $main->getLv($args[0])) !== false){
			$value = (int)$args[1] + $lv;
			if($value > 99999999999){ //add最大値を勝手に設定
				$value = 99999999999;
				$sender->sendMessage($main->getText("command.addlv.overmax"));
			}
			$bool = false;
			if($main->getServer()->getPlayer($args[0]) !== null){
				$bool = true;
			}
			$main->setLv($args[0], $value, $bool, "main-addlvcommand");
			$sender->sendMessage($main->getReplacedText("command.addlv.success", [$args[0], (int)$args[1], $value]));
		}else{
			$sender->sendMessage($main->getReplacedText("error.notfound", [$args[0]]));
		}
		return true;
	}
}