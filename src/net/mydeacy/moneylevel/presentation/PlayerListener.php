<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\presentation;

use net\mydeacy\moneylevel\application\LevelService;
use pocketmine\event\EventHandler;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

final class PlayerListener implements Listener {
    private LevelService $service;
    private NameTagService $tagService;

    public function __construct(LevelService $service, NameTagService $tagService) {
        $this->service = $service;
        $this->tagService = $tagService;
    }

    #[EventHandler(priority: EventPriority::HIGHEST)]
    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $entry = $this->service->ensure($player->getName(), $player->getDisplayName());
        $this->tagService->apply($player, $entry->getLevel());
    }
}
