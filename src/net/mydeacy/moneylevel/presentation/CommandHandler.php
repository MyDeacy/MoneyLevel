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

/**
 * Command handler for MoneyLevel commands.
 */
final class CommandHandler {

	private ServerGateway $server;

	private LevelService $service;

	private EconomyGateway $economy;

	private PluginConfig $config;

	private MessageCatalog $messages;

	private NameTagService $tagService;

	/**
	 * Creates a new instance.
	 *
	 * @param ServerGateway $server Server.
	 * @param LevelService $service Service.
	 * @param EconomyGateway $economy Economy.
	 * @param PluginConfig $config Config.
	 * @param MessageCatalog $messages Messages.
	 * @param NameTagService $tagService Tag service.
	 */
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

	/**
	 * Handles command execution.
	 *
	 * @param CommandSender $sender Sender.
	 * @param Command $command Command.
	 * @param array $args Args.
	 *
	 * @return bool True on success.
	 */
	public function handle(CommandSender $sender, Command $command, array $args) :bool {
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

	private function handleLvup(CommandSender $sender, array $args) :bool {
		if (!$sender instanceof Player) {
			$sender->sendMessage($this->messages->format("notice.noconsole"));
			return true;
		}
		$amountInput = $args[0] ?? null;
		$amount = $amountInput === null ? null : $this->parsePositiveInt((string)$amountInput);
		if ($amount === null) {
			$sender->sendMessage($this->messages->format("command.lvup.error.args"));
			return true;
		}
		$requiredPerLevel = $this->config->getRequiredMoneyPerLevel();
		$totalCost = $requiredPerLevel * $amount;
		if ($totalCost > 0 && $this->economy->getMoney($sender) < $totalCost) {
			$sender->sendMessage($this->messages->format("command.lvup.error.money", [$requiredPerLevel]));
			return true;
		}
		$initialEntry = $this->service->ensure($sender->getName(), $sender->getDisplayName());
		$before = $initialEntry->getLevel();
		$requestedAfter = $before + $amount;
		$event = new LevelUpEvent($sender->getName(), $before, $requestedAfter);
		$event->call();
		if ($event->isCancelled()) {
			return true;
		}
		$after = $event->getAfterLevel();
		if ($totalCost > 0 && !$this->economy->reduceMoney($sender, $totalCost)) {
			$sender->sendMessage($this->messages->format("command.lvup.error.money", [$requiredPerLevel]));
			return true;
		}
		$updatedEntry = $this->service->setLevel($sender->getName(), $sender->getDisplayName(), $after);
		$this->tagService->apply($sender, $updatedEntry->getLevel());
		$sender->sendMessage($this->messages->format("command.lvup.success.sender",
			[$totalCost, $updatedEntry->getLevel()]));
		if ($this->config->isBroadcastNotice()) {
			$this->server->broadcastMessage(
				$this->messages->format("command.lvup.success.broadcast",
					[$sender->getName(), $updatedEntry->getLevel()])
			);
		}
		return true;
	}

	private function handleTopLv(CommandSender $sender, array $args) :bool {
		$pageInput = $args[0] ?? null;
		$parsedPage = $pageInput === null ? null : $this->parsePositiveInt((string)$pageInput);
		$requestedPage = $parsedPage ?? 1;
		$allEntries = $this->service->listAll();
		$opNames = $this->server->getOpNameMap();
		$entries = $this->config->isTopIncludeOp()
			? $allEntries
			: array_values(array_filter(
				$allEntries,
				static fn(LevelEntry $entry) :bool => !isset($opNames[$entry->getName()])
			));
		usort($entries, static function(LevelEntry $left, LevelEntry $right) :int {
			$cmp = $right->getLevel() <=> $left->getLevel();
			return $cmp !== 0 ? $cmp : ($left->getName() <=> $right->getName());
		});
		$perPage = $this->config->getRankingPageSize();
		$total = count($entries);
		$maxPage = max(1, (int)ceil($total / $perPage));
		$boundedPage = min($maxPage, $requestedPage);
		$sender->sendMessage($this->messages->raw("command.toplv.title", [$boundedPage, $maxPage]));
		$startIndex = ($boundedPage - 1) * $perPage;
		$endIndex = min($startIndex + $perPage, $total);
		if ($startIndex >= $endIndex) {
			return true;
		}
		foreach (range($startIndex, $endIndex - 1) as $index) {
			$entry = $entries[$index];
			$sender->sendMessage($this->messages->raw("command.toplv.text",
				[$index + 1, $entry->getDisplayName(), $entry->getLevel()]));
		}
		return true;
	}

	private function handleViewLv(CommandSender $sender, array $args) :bool {
		if (!isset($args[0])) {
			$sender->sendMessage($this->messages->format("command.admin.error.args"));
			return true;
		}
		$targetName = trim((string)$args[0]);
		if ($targetName === "") {
			$sender->sendMessage($this->messages->format("command.admin.error.args"));
			return true;
		}
		$entry = $this->service->find($targetName);
		if ($entry === null) {
			$sender->sendMessage($this->messages->format("error.notfound", [$targetName]));
			return true;
		}
		$sender->sendMessage($this->messages->format("command.viewlv.success",
			[$entry->getDisplayName(), $entry->getLevel()]));
		return true;
	}

	private function handleSetLv(CommandSender $sender, array $args) :bool {
		$targetInput = $args[0] ?? null;
		$levelInput = $args[1] ?? null;
		$parsedLevel = $levelInput === null ? null : $this->parseInt((string)$levelInput);
		if ($targetInput === null || $parsedLevel === null) {
			$sender->sendMessage($this->messages->format("command.admin.error.args"));
			return true;
		}
		$targetName = trim((string)$targetInput);
		if ($targetName === "") {
			$sender->sendMessage($this->messages->format("command.admin.error.args"));
			return true;
		}
		$player = $this->server->getPlayerExact($targetName);
		$displayName = $player instanceof Player ? $player->getDisplayName() : $targetName;
		$normalizedLevel = $parsedLevel < 1 ? 1 : $parsedLevel;
		if ($normalizedLevel !== $parsedLevel) {
			$sender->sendMessage($this->messages->format("command.setlv.valueerror"));
		}
		$entry = $this->service->setLevel($targetName, $displayName, $normalizedLevel);
		if ($player instanceof Player) {
			$this->tagService->apply($player, $entry->getLevel());
		}
		$sender->sendMessage($this->messages->format("command.setlv.success",
			[$entry->getDisplayName(), $entry->getLevel()]));
		return true;
	}

	private function handleHelp(CommandSender $sender) :bool {
		$sender->sendMessage($this->messages->raw("lvhelp.title"));
		$sender->sendMessage($this->messages->raw("lvhelp.lvup"));
		$sender->sendMessage($this->messages->raw("lvhelp.toplv"));
		$sender->sendMessage($this->messages->raw("lvhelp.viewlv"));
		$sender->sendMessage($this->messages->raw("lvhelp.setlv"));
		return true;
	}

	private function parseInt(string $value) :?int {
		$trimmedValue = trim($value);
		if ($trimmedValue === "" || !preg_match('/^-?\d+$/', $trimmedValue)) {
			return null;
		}
		return (int)$trimmedValue;
	}

	private function parsePositiveInt(string $value) :?int {
		$parsed = $this->parseInt($value);
		if ($parsed === null || $parsed <= 0) {
			return null;
		}
		return $parsed;
	}
}
