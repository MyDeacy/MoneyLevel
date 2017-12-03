<?php

namespace RuinPray\MoneyLevel\Commands;
//use 
use pocketmine\command\{PluginCommand, Command,CommandSender,CommandExecutor};
use RuinPray\MoneyLevel\Main;
use pocketmine\Player;

class LvupCommand extends PluginCommand implements CommandExecutor {

	public function __construct(Main $main){
		parent::__construct('lvup', $main);
		$this->setDescription("所持金を消費してレベルアップできます");
		$this->setUsage("/lvup <amount>");
		$this->setExecutor($this);
	}


	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$main = $this->getPlugin();
		if($sender instanceof Player){
			if(!isset($args[0])){
				$sender->sendMessage($main->getText("command.lvup.error.args"));
				return true;
			}
			if((int)$args[0] > 0){
				$setting = $main->getSettings();
				$price = (int)$args[0] * $setting["up-required-money"];
				$name = $sender->getName();
				if($main->eco->myMoney($name) >= $price){
					$main->eco->reduceMoney($name, $price);
					$lv = $main->getLv($name) + $args[0];

					$main->setLv($name, $lv, true, "main-lvupcommand");
					$sender->sendMessage($main->getReplacedText("command.lvup.success.sender", [$price, $lv]));

					if($setting["broadcast-notice"] === true){
						$main->noticeAll($sender, $lv);
					}
				}else{
					$sender->sendMessage($main->getReplacedText("command.lvup.error.money",[$setting["up-required-money"]]));
				}
				return true;
			}
		}else{
			$sender->sendMessage($main->getText("notice.noconsole"));
			return true;
		}

	}

}