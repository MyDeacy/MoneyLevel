<?php
declare(strict_types=1);

namespace net\mydeacy\moneylevel\infrastructure\text;

use net\mydeacy\moneylevel\infrastructure\config\PluginConfig;
use pocketmine\plugin\PluginBase;
use function fclose;
use function is_array;
use function is_resource;
use function stream_get_contents;
use function strtolower;
use function str_replace;

final class MessageCatalog {
    private const DEFAULT_PREFIX = "§r§f[§aML§f] ";

    /** @var array<string, string> */
    private array $messages;
    private string $prefix;

    /**
     * @param array<string, string> $messages
     */
    public function __construct(array $messages, string $prefix = self::DEFAULT_PREFIX) {
        $this->messages = $messages;
        $this->prefix = $prefix;
    }

    public static function fromPlugin(PluginBase $plugin, PluginConfig $config): self {
        $language = strtolower($config->getLanguage());
        $filename = ($language === "eng" || $language === "en") ? "messages/message_eng.ini" : "messages/message.ini";
        $messages = self::loadMessages($plugin, $filename);
        if ($messages === []) {
            $messages = self::loadMessages($plugin, "messages/message.ini");
        }
        return new self($messages);
    }

    public function format(string $key, array $params = [], bool $withPrefix = true): string {
        $message = $this->messages[$key] ?? $key;
        foreach ($params as $index => $value) {
            $message = str_replace("{" . $index . "}", (string) $value, $message);
        }
        return $withPrefix ? $this->prefix . $message : $message;
    }

    public function raw(string $key, array $params = []): string {
        return $this->format($key, $params, false);
    }

    /**
     * @return array<string, string>
     */
    private static function loadMessages(PluginBase $plugin, string $filename): array {
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
