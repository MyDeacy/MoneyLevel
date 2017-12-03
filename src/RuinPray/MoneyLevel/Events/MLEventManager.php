<?php
namespace RuinPray\MoneyLevel\Events;

use RuinPray\MoneyLevel\Main;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;

class MLEventManager extends PluginEvent implements Cancellable {

	private $user, $case;
	
	public function __construct(Main $main, $case, string $user){
		parent::__construct($main);
		$this->case = $case;
		$this->user = $user;
	}
	
	public function getCase(): string{
		return $this->case;
	}

	public function getUser(): string{
		return $this->user;
	}
}