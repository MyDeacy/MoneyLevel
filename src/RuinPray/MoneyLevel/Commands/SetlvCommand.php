<?php

namespace RuinPray\MoneyLevel\Commands;

use pocketmine\command\{PluginCommand, Command,CommandSender,CommandExecutor};
use RuinPray\MoneyLevel\Main;
use pocketmine\Player;

class SetlvCommand extends PluginCommand implements CommandExecutor {

	public function __construct(Main $main){
		parent::__construct('setlv', $main);
		$this->setPermission('molcmdop');
		$this->setDescription("プレイヤーのレベルをセットします。");
		$this->setUsage("/setlv <name> <lv>");
		$this->setExecutor($this);
	}


	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$main = $this->getPlugin();
		if(!isset($args[1])){
			$sender->sendMessage($main->getText("command.admin.error.args"));
			return false;
		}
		if($main->getLv($args[0]) !== false){
			if((int)$args[1] < 0){
				$args[1] = 1;
				$sender->sendMessage($main->getText("command.setlv.valueerror"));
			}
			$bool = false;
			if($main->getServer()->getPlayer($args[0]) !== null){
				$bool = true;
			}
			$main->setLv((string)$args[0], (int)$args[1], $bool, "main-setlvcommand");
			$sender->sendMessage($main->getReplacedText("command.setlv.success", [$args[0], (int)$args[1]]));
		}else{
			$sender->sendMessage($main->getReplacedText("error.notfound", [$args[0]]));
		}
		return true;
	}
}