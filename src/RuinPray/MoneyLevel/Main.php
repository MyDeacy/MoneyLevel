<?php

namespace RuinPray\MoneyLevel;

use pocketmine\plugin\PluginBase;
use RuinPray\MoneyLevel\EventListener;
use RuinPray\MoneyLevel\Database\{Sqlite3Database,ConfigLoader};
use RuinPray\MoneyLevel\Events\{MoneyLvRegisterEvent, MoneyLvChangeEvent};
use pocketmine\Player;
use pocketmine\utils\Utils;

class Main extends PluginBase {

	public $version = "RE-CREATE1.1";
	
	private $msg = [];
	public $tag = []; //競合防止

	const Prefix = "§r§f[§aML§f] ";
	

	public function onLoad(){
		$this->getServer()->getPluginManager()->enablePlugin($this);
	}


	public function onEnable(){
		$this->cf = new ConfigLoader($this);
		$this->db = new Sqlite3Database($this->getDataFolder(), $this);

		$this->eco = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");

		$this->dir = __DIR__."/TextResource/messages.ini";

		if(!$this->loadMessageFile()){
			$this->getLogger()->emergency(">>> メッセージファイルの読み込みに失敗しました。");
			$this->getServer()-forceShutdown();
		}
		$this->registerCommands();
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		if($this->checkUpdate() !== $this->version){
			$this->getLogger()->notice("最新バージョンがリリースされています！ https://github.com/gigantessbeta/MoneyLevel/releases");
		}
	}

	private function checkUpdate(){
		$result = Utils::getURL("https://raw.githubusercontent.com/gigantessbeta/VersionList/master/MoneyLevel.txt");
		if($result == false){
			return $result;
		}
		return rtrim($result, "\n");
	}



	public function registerUser(string $name, $case = "other"){
		$this->getServer()->getPluginManager()->callEvent($event = new MoneyLvRegisterEvent($this, $name, $case));
		if(!$event->isCancelled()){
			$this->db->registerUser($name);
		}
	}

	public function getLv(string $name){
		return $this->db->getLv(strtolower($name));
	}

	public function getDefName(string $name){
		return $this->db->getDefName(strtolower($name));
	}


	public function setLv(string $name, int $lv, $tagchange = false, $case = "other"){
		$this->getServer()->getPluginManager()->callEvent($event = new MoneyLvChangeEvent($this, $name, $lv, $this->getLv($name), $case));
		if(!$event->isCancelled()){
			$this->db->setLv(strtolower($name), $lv);
			if($tagchange === true){
				$this->setLvTag($this->getServer()->getPlayer($name), $lv, true);
			}
		}
	}


	public function noticeAll($player, int $lv){
		$this->getServer()->broadcastTip($this->getReplacedText("command.lvup.success.broadcast", [$player->getName(), $lv], false));
	}

	public function setLvTag($player, int $lv, $sign = false){
		$tag = $this->getReplacedText("player.tag", [$lv], false);
		$name = $player->getName();
		if(!$sign){
			$this->tag[$name] = $player->getNameTag();
		}
		$tag = $tag.$this->tag[$name];
		$player->setNameTag($tag);
		$player->setDisplayName($tag);
	}



	private function loadMessageFile(): bool{
		$this->msg = parse_ini_file($this->dir);
		return true;
	}


	public function getReplacedText(string $key, array $vaa = [], $prefix = true){
		$text = $this->getText($key, false);
		if($text !== false){
			foreach($vaa as $a => $af){
				$text = str_replace("{".$a."}", $af, $text);
			}
			if($prefix){
				$text = Main::Prefix.$text;
			}
			return $text;
		}
		return false;
	}


	public function getText(string $key, $prefix = true){
		if(isset($this->msg[$key])){
			$text = $this->msg[$key];
			if($prefix){
				$text = Main::Prefix.$text;
			}
			return $text;
		}
		$this->getLogger()->warning("メッセージファイルに定義されていないメッセージを呼び出そうとしました。");
		return false;
	}


	public function getSettings(): array{
		return $this->cf->getSettings();
	}

	public function getAllData(): array{
		return $this->db->getAllData();
	}


	private function registerCommands(){
		$this->getServer()->getCommandMap()->register("moneylevel", new Commands\LvupCommand($this));
		$this->getServer()->getCommandMap()->register('moneylevel', new Commands\ToplvCommand($this));
		$this->getServer()->getCommandMap()->register("moneylevel", new Commands\ViewLvCommand($this));
		$this->getServer()->getCommandMap()->register('moneylevel', new Commands\SetlvCommand($this));
		$this->getServer()->getCommandMap()->register("moneylevel", new Commands\TakelvCommand($this));
		$this->getServer()->getCommandMap()->register('moneylevel', new Commands\AddlvCommand($this));
		$this->getServer()->getCommandMap()->register('moneylevel', new Commands\lvhelpCommand($this));
		
	}
} 