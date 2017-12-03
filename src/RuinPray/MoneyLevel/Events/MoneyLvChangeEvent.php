<?php
namespace RuinPray\MoneyLevel\Events;

use RuinPray\MoneyLevel\Main;
use RuinPray\MoneyLevel\Events\MLEventManager;

class MoneyLvChangeEvent extends MLEventManager {

	public static $handlerList;

	protected $amount, $before;

	public function __construct(Main $main, string $user, int $amount, int $before, $case){
		parent::__construct($main, $user, $case);
		$this->amount = $amount;
		$this->before = $before;
	}

	public function getChangedLv(): int{
		return abs($this->before - $this->amount); //増減値を絶対値で返す
	}

	public function getLv(): int{
		return $this->amount;
	}

	public function getBeforeLv(): int{
		return $this->before;
	}
}