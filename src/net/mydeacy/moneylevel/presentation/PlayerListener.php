<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\presentation;

use net\mydeacy\moneylevel\application\LevelService;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

/**
 * Player event listener.
 */
final class PlayerListener implements Listener {

	private LevelService $service;

	private NameTagService $tagService;

	/**
	 * Creates a new instance.
	 *
	 * @param LevelService $service Service.
	 * @param NameTagService $tagService Tag service.
	 */
	public function __construct(LevelService $service, NameTagService $tagService) {
		$this->service = $service;
		$this->tagService = $tagService;
	}

	/**
	 * Handles join.
	 *
	 * @priority HIGHEST
	 * @param PlayerJoinEvent $event Event.
	 */
	public function onJoin(PlayerJoinEvent $event) :void {
		$player = $event->getPlayer();
		$entry = $this->service->ensure($player->getName(), $player->getDisplayName());
		$this->tagService->apply($player, $entry->getLevel());
	}
}
