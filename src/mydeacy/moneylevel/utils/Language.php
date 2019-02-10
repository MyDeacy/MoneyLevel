<?php

namespace mydeacy\moneylevel\utils;

use mydeacy\moneylevel\service\MoneyLevelAPI;
use pocketmine\utils\MainLogger;

class Language {

	private $messages;

	public function __construct($language){
		$dir = __DIR__."/messages/message";
		$extension = $language !== "jpn" ? "_eng.ini" : ".ini";
		$this->messages = parse_ini_file($dir.$extension);
	}


	public function getReplacedText(string $key, array $replaces = [], $prefix = true){
		$text = $this->getText($key, false);
		if($text !== false){
			foreach($replaces as $base => $replace){
				$text = str_replace("{".$base."}", $replace, $text);
			}
			if($prefix){
				$text = MoneyLevelAPI::Prefix.$text;
			}
			return $text;
		}
		return false;
	}

	public function getText(string $key, $prefix = true){
		if(isset($this->messages[$key])){
			$text = $this->messages[$key];
			if($prefix){
				$text = MoneyLevelAPI::Prefix.$text;
			}
			return $text;
		}
		MainLogger::getLogger()->warning("メッセージファイルに定義されていないメッセージを呼び出そうとしました。");
		return false;
	}


}