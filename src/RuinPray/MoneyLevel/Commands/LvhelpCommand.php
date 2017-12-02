<?php

namespace RuinPray\MoneyLevel\Commands;
//use 
use pocketmine\command\{PluginCommand, Command,CommandSender,CommandExecutor};
use RuinPray\MoneyLevel\Main;
use pocketmine\Player;

class LvhelpCommand extends PluginCommand implements CommandExecutor {

	const Commands = ["lvup", "toplv", "viewlv"];
	const OpCmds = ["addlv", "takelv", "setlv"];

	public function __construct(Main $main){
		parent::__construct('lvhelp', $main);
		$this->setDescription("所持金を消費してレベルアップできます");
		$this->setUsage("/lvhelp");
		$this->setExecutor($this);
	}


	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$main = $this->getPlugin();
		$sender->sendMessage($main->getText("lvhelp.title", false));

		foreach(LvhelpCommand::Commands as $cmdname){
			$sender->sendMessage($main->getText("lvhelp.".$cmdname, false));
		}
		if($sender->isOp()){
			foreach(LvhelpCommand::OpCmds as $cmdname){
				$sender->sendMessage($main->getText("lvhelp.".$cmdname, false));	
			}
		}
		return true;
	}
}