<?php
namespace RuinPray\MoneyLevel\Events;

use RuinPray\MoneyLevel\Main;
use RuinPray\MoneyLevel\Events\MLEventManager;

class MoneyLvRegisterEvent extends MLEventManager {

	public static $handlerList;

	public function __construct(Main $main, string $user, $case){
		parent::__construct($main, $user, $case);
	}
}