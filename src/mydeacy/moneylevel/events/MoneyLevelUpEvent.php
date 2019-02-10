<?php

namespace mydeacy\moneylevel\events;

use mydeacy\moneylevel\MoneyLevel;

class MoneyLevelUpEvent extends MoneyLevelEventBase {

	protected $amount, $before;

	public function __construct(MoneyLevel $main, string $user, int $amount, int $before){
		parent::__construct($main, $user);
		$this->amount = $amount;
		$this->before = $before;
	}

	/**
	 * @return int
	 */
	public function getChangedLv(): int{
		return abs($this->before - $this->amount);
	}

	/**
	 * @return int
	 */
	public function getLv(): int{
		return $this->amount;
	}

	/**
	 * @return int
	 */
	public function getBeforeLv(): int{
		return $this->before;
	}

}