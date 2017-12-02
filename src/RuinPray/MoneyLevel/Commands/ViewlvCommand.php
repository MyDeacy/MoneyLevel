<?php

namespace RuinPray\MoneyLevel\Commands;

use pocketmine\command\{PluginCommand, Command,CommandSender,CommandExecutor};
use RuinPray\MoneyLevel\Main;
use pocketmine\Player;

class ViewlvCommand extends PluginCommand implements CommandExecutor {

	public function __construct(Main $main){
		parent::__construct('viewlv', $main);
		$this->setDescription("経済レベルを確認できます。");
		$this->setUsage("/viewlv <name>");
		$this->setExecutor($this);
	}


	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$main = $this->getPlugin();
		if(!isset($args[0])){
			if(!$sender instanceof Player){
				$sender->sendMessage($main->getText("notice.noconsole"));
				return true;
			}
			$args[0] = $sender->getName();
		}
		$name = $args[0];
		if(($lv = $main->getLv($name)) === false){
			$sender->sendMessage($main->getReplacedText("error.notfound", [$name]));
		}else{
			$sender->sendMessage($main->getReplacedText("command.viewlv.success", [$main->getDefName($name), $lv]));
		}
		return true;
	}
}