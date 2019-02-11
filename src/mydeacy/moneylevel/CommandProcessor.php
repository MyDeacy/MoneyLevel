<?php

namespace mydeacy\moneylevel;

use mydeacy\moneylevel\service\MoneyLevelAPI;
use mydeacy\moneylevel\tasks\RankingTask;
use onebone\economyapi\EconomyAPI;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

class CommandProcessor {

	const Commands = ["lvup", "toplv", "viewlv"];

	const OpCmds = ["setlv"];

	private $api, $economy;

	public function __construct(MoneyLevelAPI $api){
		$this->api = $api;
		$this->economy = EconomyAPI::getInstance();
	}

	function onCommand(CommandSender $sender, String $label, array $args): bool{
		$lang = $this->api->getLanguage();
		if(!$sender instanceof Player){
			$sender->sendMessage($lang->getText("notice.noconsole"));
			return true;
		}
		$config = $this->api->getConfig();
		$economy = $this->economy;
		$api = $this->api;
		switch(strtolower($label)){
			case "lvup":
				if(!isset($args[0])){
					$sender->sendMessage($lang->getText("command.lvup.error.args"));
					return false;
				}
				$amount = (int)$args[0];
				if($amount > 0){
					$price = $amount * $config->get("up-required-money");
					$name = $sender->getName();
					if($economy->myMoney($name) < $price){
						$sender->sendMessage($lang->getReplacedText("command.lvup.error.money",
							[$config->get("up-required-money")]));
						return true;
					}
					try{
						$economy->reduceMoney($name, $price);
					}catch(\ReflectionException $e){ //ないはずけど念のため
						$sender->sendMessage("Player is not found");
					}
					$api->lvUp($name, $amount);
					$level = $api->getLv($name);
					$sender->sendMessage($lang->getReplacedText("command.lvup.success.sender",
						[$price, $level]));
					if($config->get("broadcast-notice") === true){
						$api->noticeAll($sender, $level);
					}
				}
				return true;
			case "toplv":
				$page = @$args[0] ?: 1;
				$task = new RankingTask($api->getAll(), $page, $sender->getName(),
					Server::getInstance()->getOps()->getAll(), $config->get("toplv-enable-op"));
				Server::getInstance()->getAsyncPool()->submitTask($task);
				return true;
			case "viewlv":
				if(!isset($args[0])){
					$args[0] = $sender->getName();
				}
				$name = $args[0];
				if(empty(($lv = $api->getLv($name)))){
					$sender->sendMessage($lang->getReplacedText("error.notfound", [$name]));
				}else{
					$sender->sendMessage($lang->getReplacedText("command.viewlv.success",
						[$api->getDefName($name), $lv]));
				}
				return true;
			case "setlv":
				if(!isset($args[1])){
					$sender->sendMessage($lang->getText("command.admin.error.args"));
					return false;
				}
				$name = $args[0];
				if($api->getLv($name) !== false){
					$level = (int)$args[1];
					if($level < 0 || $level > PHP_INT_MAX){
						$args[1] = 1;
						$sender->sendMessage($lang->getText("command.setlv.valueerror"));
					}
					$before = $api->getLv($name);
					$api->setLv((string)$name, $level);
					if(!empty($player = Server::getInstance()->getPlayer($name))){
						$api->setLvTag($player, $level, $before);
					}
					$sender->sendMessage($lang->getReplacedText("command.setlv.success",
						[$name, $level]));
				}else{
					$sender->sendMessage($lang->getReplacedText("error.notfound", [$name]));
				}
				return true;
			case "lvhelp":
				$sender->sendMessage($lang->getText("lvhelp.title", false));
				foreach(self::Commands as $cmdName){
					$sender->sendMessage($lang->getText("lvhelp.".$cmdName, false));
				}
				if($sender->isOp()){
					foreach(self::OpCmds as $cmdName){
						$sender->sendMessage($lang->getText("lvhelp.".$cmdName, false));
					}
				}
				return true;
		}
		return false;
	}
}