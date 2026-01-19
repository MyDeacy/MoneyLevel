<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\text;

use net\mydeacy\moneylevel\infrastructure\config\PluginConfig;
use pocketmine\plugin\PluginBase;
use function fclose;
use function is_array;
use function is_resource;
use function str_replace;
use function stream_get_contents;
use function strtolower;

/**
 * Message catalog.
 */
final class MessageCatalog {

	private const DEFAULT_PREFIX = "§r§f[§aML§f] ";

	/** @var array<string, string> */
	private array $messages;

	private string $prefix;

	/**
	 * Creates a new instance.
	 *
	 * @param array<string, string> $messages Messages.
	 * @param string $prefix Prefix.
	 */
	public function __construct(array $messages, string $prefix = self::DEFAULT_PREFIX) {
		$this->messages = $messages;
		$this->prefix = $prefix;
	}

	/**
	 * Loads messages from plugin resources.
	 *
	 * @param PluginBase $plugin Plugin.
	 * @param PluginConfig $config Config.
	 *
	 * @return self
	 */
	public static function fromPlugin(PluginBase $plugin, PluginConfig $config) :self {
		$language = strtolower($config->getLanguage());
		$filename = ($language === "eng" || $language === "en") ? "messages/message_eng.ini" : "messages/message.ini";
		$primaryMessages = self::loadMessages($plugin, $filename);
		$messages = $primaryMessages === [] ? self::loadMessages($plugin, "messages/message.ini") : $primaryMessages;
		return new self($messages);
	}

	/**
	 * Formats a message with parameters.
	 *
	 * @param string $key Key.
	 * @param array $params Params.
	 * @param bool $withPrefix With prefix.
	 *
	 * @return string
	 */
	public function format(string $key, array $params = [], bool $withPrefix = true) :string {
		$message = $this->messages[$key] ?? $key;
		$search = [];
		$replace = [];
		foreach ($params as $index => $value) {
			$search[] = "{" . $index . "}";
			$replace[] = (string)$value;
		}
		$formatted = $search === [] ? $message : str_replace($search, $replace, $message);
		return $withPrefix ? $this->prefix . $formatted : $formatted;
	}

	/**
	 * Returns a raw message template.
	 *
	 * @param string $key Key.
	 * @param array $params Params.
	 *
	 * @return string
	 */
	public function raw(string $key, array $params = []) :string {
		return $this->format($key, $params, false);
	}

	/**
	 * Loads messages from a resource file.
	 *
	 * @return array<string, string>
	 */
	private static function loadMessages(PluginBase $plugin, string $filename) :array {
		$resource = $plugin->getResource($filename);
		if ($resource === null || !is_resource($resource)) {
			return [];
		}
		$content = stream_get_contents($resource);
		fclose($resource);
		$parsed = parse_ini_string($content ?: "", false, INI_SCANNER_RAW);
		return is_array($parsed) ? $parsed : [];
	}
}
