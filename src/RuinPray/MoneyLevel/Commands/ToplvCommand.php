<?php

namespace RuinPray\MoneyLevel\Commands;

use pocketmine\command\{PluginCommand, Command,CommandSender,CommandExecutor};
use RuinPray\MoneyLevel\Main;
use pocketmine\Player;

class ToplvCommand extends PluginCommand implements CommandExecutor {

	public function __construct(Main $main){
		parent::__construct('toplv', $main);
		$this->setDescription("経済レベルランキングを表示します。");
		$this->setUsage("/toplv <page>");
		$this->setExecutor($this);
	}


	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
		$main = $this->getPlugin();
		if(!isset($args[0])){
			$args[0] = 1;
		}

		$db = $main->getAllData();
		$all = [];
		foreach($db as $num => $va){
			if($va["defname"] === "-"){
				$va["defname"] = $va["name"];
			}
			$all += [$va["defname"] => $va["level"]];
		}

		//TODO  AsyncTaskでの実行

		$max = 0;
		$max = count($all);
		$max = ceil(($max / 5));
		$page = max(1, (int)$args[0]);
		$page = min($max, $page);
		$page = (int) $page;
		$sender->sendMessage($main->getReplacedText("command.toplv.title", [$page, $max], false));
		arsort($all);
		$oprank = $main->getSettings()["toplv-enable-op"];
		$i = 1;

		foreach($all as $user => $lv){
			$usert = strtolower($user);
			if(isset($main->getServer()->getOps()->getAll()[$usert]) && $oprank === false){
				continue;
			}
			if(($page - 1) * 5 <= $i && $i <= ($page - 1) * 5 + 4){
				$sender->sendMessage($main->getReplacedText("command.toplv.text", [$i, $user, $lv], false));
			}
			$i++;
		}
		return true;
	}
}