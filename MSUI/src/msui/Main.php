<?php
namespace msui;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use msui\command\OpenUICommand;
use msui\command\DebugCommand;
use msui\event\form\MainUIReceive;
use msui\event\player\JoinPlayer;
use msui\event\money\MoneyEventHandler;

class Main extends PluginBase
{
    /** @var array */
	private static $lang = [];
    /* @var array */
    private static $history = [];
    /** @var array */
    private static $config = [];
    /** @var Config */
    private $historyFile;

    private static $colorTag = [
        "[COLOR: BLACK]",
        "[COLOR: DARK_BLUE]",
        "[COLOR: DARK_GREEN]",
        "[COLOR: DARK_AQUA]",
        "[COLOR: DARK_RED]",
        "[COLOR: DARK_PURPLE]",
        "[COLOR: GOLD]",
        "[COLOR: GRAY]",
        "[COLOR: DARK_GRAY]",
        "[COLOR: BLUE]",
        "[COLOR: GREEN]",
        "[COLOR: AQUA]",
        "[COLOR: RED]",
        "[COLOR: LIGHT_PURPLE]",
        "[COLOR: YELLOW]",
        "[COLOR: WHITE]",
        "[COLOR: OBFUSCATED]",
        "[COLOR: BOLD]",
        "[COLOR: STRIKETHROUGH]",
        "[COLOR: UNDERLINE]",
        "[COLOR: ITALIC]",
        "[COLOR: RESET]"
    ];

    private static $color = [
        TextFormat::BLACK,
        TextFormat::DARK_BLUE,
        TextFormat::DARK_GREEN,
        TextFormat::DARK_AQUA,
        TextFormat::DARK_RED,
        TextFormat::DARK_PURPLE,
        TextFormat::GOLD,
        TextFormat::GRAY,
        TextFormat::DARK_GRAY,
        TextFormat::BLUE,
        TextFormat::GREEN,
        TextFormat::AQUA, 
        TextFormat::RED,
        TextFormat::LIGHT_PURPLE,
        TextFormat::YELLOW,
        TextFormat::WHITE,
        TextFormat::OBFUSCATED,
        TextFormat::BOLD,
        TextFormat::STRIKETHROUGH,
        TextFormat::UNDERLINE,
        TextFormat::ITALIC,
        TextFormat::RESET
    ];

	public function onEnable() : void
	{
        $this->initFiles();
        $this->registerCommands();
        $this->registerEvents();
        date_default_timezone_set('Asia/Tokyo');
	}

    public function onDisable() : void
    {
        $this->getLogger()->info("MSUIをシャットダウン中...");
        $this->historyFile->setAll(self::$history);
        $this->historyFile->save();
    }

    private function initFiles() : void
    {
        $this->saveResource("Config.yml", false);
        $this->saveResource("Language.yml", false);
        self::$lang = (new Config($this->getDataFolder() . "Language.yml", Config::YAML))->getAll();
        self::$config = (new Config($this->getDataFolder() . "Config.yml", Config::YAML))->getAll();

        if (self::$config["history-file-save"]) {
            $this->historyFile = new Config($this->getDataFolder() . "History.yml", Config::YAML);
            self::$history = $this->historyFile->getAll();
        }
    }

    private function registerEvents()
    {
        $this->getServer()->getPluginManager()->registerEvents(new MoneyEventHandler, $this);
        $this->getServer()->getPluginManager()->registerEvents(new JoinPlayer($this), $this);
    }

    private function registerCommands() : void
    {
        $this->getServer()->getCommandMap()->register("msys", new OpenUICommand($this));
        $this->getServer()->getCommandMap()->register("msdebug", new DebugCommand($this));
    }

    public static function getConfigData() : array
    {
        return self::$config;
    }

    public static function getMessage(string $key, array $input = []) : string
    {
        if (!isset(self::$lang[$key])) {
            return TextFormat::RED . "The character string \"" . TextFormat::YELLOW . $key . TextFormat::RED . "\" could not be found from the search result database.";
        }
        $message = str_replace(["[EOL]", "[EOLL]"], ["\n" . str_pad(" ", 33), "\n"], self::$lang[$key]);
        $message = str_replace(self::$colorTag, self::$color, $message);
        if (!empty($input)) {
            $count = (int) count($input);
            for ($i = 0; $i < $count; ++$i) {
                $search[] = '[TAG: NO.' . $i . ']';
                $replacement[] = $input[$i];
            }
            return str_replace($search, $replacement, $message);
        } else {
            return $message;
        }
    }

    public static function addHistory(MoneyEventHandler $handler, array $data) : void
    {
        self::$history[] = $data;
        if (count(self::$history) > self::$config["number-to-save-history"]) {
            array_shift(self::$history);
        }
    }

    public static function getHistory(MainUIReceive $caller) : array
    {
        return array_reverse(self::$history);
    }
}
