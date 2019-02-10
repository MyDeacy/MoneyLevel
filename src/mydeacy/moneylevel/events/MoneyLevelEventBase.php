<?php

namespace mydeacy\moneylevel\events;

use mydeacy\moneylevel\MoneyLevel;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;

class MoneyLevelEventBase extends PluginEvent implements Cancellable {

	public static $handlerList;

	private $user;

	public function __construct(MoneyLevel $main, string $user){
		parent::__construct($main);
		$this->user = $user;
	}

	public function getUser(): string{
		return $this->user;
	}

}