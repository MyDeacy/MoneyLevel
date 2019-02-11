<?php

namespace mydeacy\moneylevel\events;

use mydeacy\moneylevel\MoneyLevel;

class MoneyLevelUpEvent extends MoneyLevelEventBase {

	protected $level, $before;

	public function __construct(MoneyLevel $main, string $user, int $level, int $before){
		parent::__construct($main, $user);
		$this->level = $level;
		$this->before = $before;
	}

	/**
	 * @return int
	 */
	public function getChangedLv(): int{
		return $this->before - $this->level;
	}

	/**
	 * @return int
	 */
	public function getLv(): int{
		return $this->level;
	}

	/**
	 * @return int
	 */
	public function getBeforeLv(): int{
		return $this->before;
	}

}