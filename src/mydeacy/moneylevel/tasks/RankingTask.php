<?php

namespace mydeacy\moneylevel\tasks;

use mydeacy\moneylevel\service\MoneyLevelAPI;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class RankingTask extends AsyncTask {

	private $data, $page, $name, $ops, $opRank;

	public function __construct(array $data, int $page, String $name, array $ops, bool $opRank){
		$this->data = $data;
		$this->page = $page;
		$this->name = $name;
		$this->ops = $ops;
		$this->opRank = $opRank;
	}

	public function onRun(): void{
		$all = [];
		foreach($this->data as $num => $value){
			$all += [$value["defName"] => $value["level"]];
		}
		$max = count($all);
		$max = ceil(($max / 5));
		$page = max(1, $this->page);
		$page = min($max, $page);
		$page = (int)$page;
		arsort($all);
		$i = 1;
		$ranking = [];
		foreach($all as $user => $lv){
			$usert = strtolower($user);
			if(isset($this->ops[$usert]) && !$this->opRank){
				continue;
			}
			if(($page - 1) * 5 <= $i && $i <= ($page - 1) * 5 + 5){
				$ranking[] = [$i, $user, $lv];
			}
			$i++;
		}
		$this->setResult(serialize([$ranking, $max]));
	}

	public function onCompletion(Server $server): void{
		$result = $this->getResult();
		if($server->getPluginManager()->getPlugin("MoneyLevel")->isEnabled()){
			$player = $server->getPlayer($this->name);
			if(!empty($player)){
				$api = MoneyLevelAPI::getInstance();
				$lang = $api->getLanguage();
				$text = $lang->getReplacedText("command.toplv.title", [$this->page, $result[1]],
					false);
				foreach($result[0] as $arr){
					$text .= "\n".$lang->getReplacedText("command.toplv.text",
							[$arr[0], $api->getDefName($arr[1]), $arr[2]], false);
				}
				$player->sendMessage($text);
			}
		}
	}
}
