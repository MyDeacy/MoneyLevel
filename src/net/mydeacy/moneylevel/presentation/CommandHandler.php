<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\presentation;

use net\mydeacy\moneylevel\application\LevelService;
use net\mydeacy\moneylevel\domain\LevelEntry;
use net\mydeacy\moneylevel\event\LevelUpEvent;
use net\mydeacy\moneylevel\infrastructure\config\PluginConfig;
use net\mydeacy\moneylevel\infrastructure\economy\EconomyGateway;
use net\mydeacy\moneylevel\infrastructure\text\MessageCatalog;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use function array_filter;
use function array_values;
use function ceil;
use function count;
use function max;
use function preg_match;
use function strtolower;
use function trim;
use function usort;

final class CommandHandler {
    private ServerGateway $server;
    private LevelService $service;
    private EconomyGateway $economy;
    private PluginConfig $config;
    private MessageCatalog $messages;
    private NameTagService $tagService;

    public function __construct(
        ServerGateway $server,
        LevelService $service,
        EconomyGateway $economy,
        PluginConfig $config,
        MessageCatalog $messages,
        NameTagService $tagService
    ) {
        $this->server = $server;
        $this->service = $service;
        $this->economy = $economy;
        $this->config = $config;
        $this->messages = $messages;
        $this->tagService = $tagService;
    }

    public function handle(CommandSender $sender, Command $command, array $args): bool {
        switch (strtolower($command->getName())) {
            case "lvup":
                return $this->handleLvup($sender, $args);
            case "toplv":
                return $this->handleTopLv($sender, $args);
            case "viewlv":
                return $this->handleViewLv($sender, $args);
            case "setlv":
                return $this->handleSetLv($sender, $args);
            case "lvhelp":
                return $this->handleHelp($sender);
            default:
                return false;
        }
    }

    private function handleLvup(CommandSender $sender, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->messages->format("notice.noconsole"));
            return true;
        }

        if (!isset($args[0]) || ($amount = $this->parsePositiveInt((string) $args[0])) === null) {
            $sender->sendMessage($this->messages->format("command.lvup.error.args"));
            return true;
        }

        $requiredPerLevel = $this->config->getRequiredMoneyPerLevel();
        $totalCost = $requiredPerLevel * $amount;
        if ($totalCost > 0 && $this->economy->getMoney($sender) < $totalCost) {
            $sender->sendMessage($this->messages->format("command.lvup.error.money", [$requiredPerLevel]));
            return true;
        }

        $entry = $this->service->ensure($sender->getName(), $sender->getDisplayName());
        $before = $entry->getLevel();
        $after = $before + $amount;

        $event = new LevelUpEvent($sender->getName(), $before, $after);
        $event->call();
        if ($event->isCancelled()) {
            return true;
        }

        $after = $event->getAfterLevel();
        if ($totalCost > 0 && !$this->economy->reduceMoney($sender, $totalCost)) {
            $sender->sendMessage($this->messages->format("command.lvup.error.money", [$requiredPerLevel]));
            return true;
        }

        $entry = $this->service->setLevel($sender->getName(), $sender->getDisplayName(), $after);
        $this->tagService->apply($sender, $entry->getLevel());

        $sender->sendMessage($this->messages->format("command.lvup.success.sender", [$totalCost, $entry->getLevel()]));
        if ($this->config->isBroadcastNotice()) {
            $this->server->broadcastMessage(
                $this->messages->format("command.lvup.success.broadcast", [$sender->getName(), $entry->getLevel()])
            );
        }

        return true;
    }

    private function handleTopLv(CommandSender $sender, array $args): bool {
        $page = 1;
        if (isset($args[0])) {
            $parsed = $this->parsePositiveInt((string) $args[0]);
            if ($parsed !== null) {
                $page = $parsed;
            }
        }

        $entries = $this->service->listAll();
        if (!$this->config->isTopIncludeOp()) {
            $opNames = $this->server->getOpNameMap();
            $entries = array_values(array_filter(
                $entries,
                static fn(LevelEntry $entry): bool => !isset($opNames[$entry->getName()])
            ));
        }

        usort($entries, static function (LevelEntry $left, LevelEntry $right): int {
            $cmp = $right->getLevel() <=> $left->getLevel();
            return $cmp !== 0 ? $cmp : ($left->getName() <=> $right->getName());
        });

        $perPage = $this->config->getRankingPageSize();
        $total = count($entries);
        $maxPage = max(1, (int) ceil($total / $perPage));
        if ($page > $maxPage) {
            $page = $maxPage;
        }

        $sender->sendMessage($this->messages->raw("command.toplv.title", [$page, $maxPage]));
        $start = ($page - 1) * $perPage;
        $end = min($start + $perPage, $total);
        for ($i = $start; $i < $end; $i++) {
            $entry = $entries[$i];
            $sender->sendMessage($this->messages->raw("command.toplv.text", [$i + 1, $entry->getDisplayName(), $entry->getLevel()]));
        }

        return true;
    }

    private function handleViewLv(CommandSender $sender, array $args): bool {
        if (!isset($args[0])) {
            $sender->sendMessage($this->messages->format("command.admin.error.args"));
            return true;
        }

        $target = trim((string) $args[0]);
        if ($target === "") {
            $sender->sendMessage($this->messages->format("command.admin.error.args"));
            return true;
        }
        $entry = $this->service->find($target);
        if ($entry === null) {
            $sender->sendMessage($this->messages->format("error.notfound", [$target]));
            return true;
        }

        $sender->sendMessage($this->messages->format("command.viewlv.success", [$entry->getDisplayName(), $entry->getLevel()]));
        return true;
    }

    private function handleSetLv(CommandSender $sender, array $args): bool {
        if (!isset($args[0], $args[1]) || ($level = $this->parseInt((string) $args[1])) === null) {
            $sender->sendMessage($this->messages->format("command.admin.error.args"));
            return true;
        }

        $target = trim((string) $args[0]);
        if ($target === "") {
            $sender->sendMessage($this->messages->format("command.admin.error.args"));
            return true;
        }

        $player = $this->server->getPlayerExact($target);
        $displayName = $player instanceof Player ? $player->getDisplayName() : $target;
        if ($level < 1) {
            $level = 1;
            $sender->sendMessage($this->messages->format("command.setlv.valueerror"));
        }

        $entry = $this->service->setLevel($target, $displayName, $level);
        if ($player instanceof Player) {
            $this->tagService->apply($player, $entry->getLevel());
        }

        $sender->sendMessage($this->messages->format("command.setlv.success", [$entry->getDisplayName(), $entry->getLevel()]));
        return true;
    }

    private function handleHelp(CommandSender $sender): bool {
        $sender->sendMessage($this->messages->raw("lvhelp.title"));
        $sender->sendMessage($this->messages->raw("lvhelp.lvup"));
        $sender->sendMessage($this->messages->raw("lvhelp.toplv"));
        $sender->sendMessage($this->messages->raw("lvhelp.viewlv"));
        $sender->sendMessage($this->messages->raw("lvhelp.setlv"));
        return true;
    }

    private function parseInt(string $value): ?int {
        $value = trim($value);
        if ($value === "" || !preg_match('/^-?\d+$/', $value)) {
            return null;
        }
        return (int) $value;
    }

    private function parsePositiveInt(string $value): ?int {
        $parsed = $this->parseInt($value);
        if ($parsed === null || $parsed <= 0) {
            return null;
        }
        return $parsed;
    }
}
