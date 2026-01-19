<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel;

use net\mydeacy\moneylevel\api\MoneyLevelAPI;
use net\mydeacy\moneylevel\application\LevelService;
use net\mydeacy\moneylevel\infrastructure\config\PluginConfig;
use net\mydeacy\moneylevel\infrastructure\economy\EconomyApiGateway;
use net\mydeacy\moneylevel\infrastructure\economy\EconomyGateway;
use net\mydeacy\moneylevel\infrastructure\persistence\SqliteLevelRepository;
use net\mydeacy\moneylevel\infrastructure\server\PmmpServerGateway;
use net\mydeacy\moneylevel\infrastructure\text\MessageCatalog;
use net\mydeacy\moneylevel\presentation\CommandHandler;
use net\mydeacy\moneylevel\presentation\NameTagService;
use net\mydeacy\moneylevel\presentation\PlayerListener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use RuntimeException;
use function is_dir;
use function mkdir;

/**
 * Main plugin entrypoint.
 */
final class MoneyLevelPlugin extends PluginBase {

	private static ?self $instance = null;

	private ?PluginConfig $configModel = null;

	private ?LevelService $levelService = null;

	private ?MessageCatalog $messages = null;

	private ?NameTagService $tagService = null;

	private ?EconomyGateway $economy = null;

	private ?CommandHandler $commandHandler = null;

	private ?MoneyLevelAPI $api = null;

	/**
	 * Returns the plugin instance if loaded.
	 */
	public static function getInstance() :?self {
		return self::$instance;
	}

	/**
	 * Handles load.
	 */
	protected function onLoad() :void {
		self::$instance = $this;
	}

	/**
	 * Handles enable.
	 */
	protected function onEnable() :void {
		if (!is_dir($this->getDataFolder())) {
			mkdir($this->getDataFolder(), 0777, true);
		}
		$this->saveDefaultConfig();
		$this->configModel = new PluginConfig($this->getConfig());
		$repository = new SqliteLevelRepository($this->getDataFolder() . "levels.sqlite");
		$this->levelService = new LevelService($repository, $this->configModel->getInitialLevel());
		$this->messages = MessageCatalog::fromPlugin($this, $this->configModel);
		$this->tagService = new NameTagService($this->messages);
		$this->api = new MoneyLevelAPI($this->levelService);
		$serverGateway = new PmmpServerGateway($this->getServer());
		try {
			$this->economy = new EconomyApiGateway($this->getServer());
		} catch (RuntimeException $e) {
			$this->getLogger()->error($e->getMessage());
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->commandHandler = new CommandHandler(
			$serverGateway,
			$this->levelService,
			$this->economy,
			$this->configModel,
			$this->messages,
			$this->tagService
		);
		$this->getServer()->getPluginManager()->registerEvents(
			new PlayerListener($this->levelService, $this->tagService),
			$this
		);
		$this->getLogger()->info($this->messages->raw("enable.plugin"));
	}

	/**
	 * Handles disable.
	 */
	protected function onDisable() :void {
		self::$instance = null;
	}

	/**
	 * Handles command.
	 *
	 * @param CommandSender $sender Sender.
	 * @param Command $command Command.
	 * @param string $label Label.
	 * @param array $args Args.
	 *
	 * @return bool True on success.
	 */
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool {
		if ($this->commandHandler === null) {
			return false;
		}
		return $this->commandHandler->handle($sender, $command, $args);
	}

	/**
	 * Returns the public MoneyLevel API.
	 *
	 * @throws RuntimeException if the plugin is not initialized.
	 */
	public function getApi() :MoneyLevelAPI {
		if ($this->api === null) {
			throw new RuntimeException("MoneyLevel is not initialized.");
		}
		return $this->api;
	}

	/**
	 * Returns the level service.
	 *
	 * @throws RuntimeException if the plugin is not initialized.
	 */
	public function getLevelService() :LevelService {
		if ($this->levelService === null) {
			throw new RuntimeException("MoneyLevel is not initialized.");
		}
		return $this->levelService;
	}

	/**
	 * Returns the message catalog.
	 *
	 * @throws RuntimeException if the plugin is not initialized.
	 */
	public function getMessages() :MessageCatalog {
		if ($this->messages === null) {
			throw new RuntimeException("MoneyLevel is not initialized.");
		}
		return $this->messages;
	}

	/**
	 * Returns the config model.
	 *
	 * @throws RuntimeException if the plugin is not initialized.
	 */
	public function getConfigModel() :PluginConfig {
		if ($this->configModel === null) {
			throw new RuntimeException("MoneyLevel is not initialized.");
		}
		return $this->configModel;
	}

	/**
	 * Returns the economy gateway.
	 *
	 * @throws RuntimeException if the plugin is not initialized.
	 */
	public function getEconomy() :EconomyGateway {
		if ($this->economy === null) {
			throw new RuntimeException("MoneyLevel is not initialized.");
		}
		return $this->economy;
	}
}
