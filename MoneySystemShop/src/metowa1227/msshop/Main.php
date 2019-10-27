<?php
namespace metowa1227\msshop;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\level\Position;
use metowa1227\msshop\event\SignTouchEventHandler;
use metowa1227\msshop\event\SignBreakEventHandler;
use metowa1227\msshop\command\SetEditModeCommand;

class Main extends PluginBase
{
    const VERSION = 5.0;

    /**
     * プラグインのデータフォルダ
     *
     * @var string
     */
    private $dataFolder;

    /**
     * SHOPの保存ファイル
     *
     * @var Config
     */
    private $shopSaveFile;

    /**
     * SHOPの保存データ
     *
     * @var array
     */
    private static $shopData;

    /**
     * 言語データ
     *
     * @var array
     */
    private static $lang;

    /**
     * 編集モードが有効かどうか
     *
     * @var boolean
     */
    private $editMode = false;

    public function enableEditMode(string $name): void  { $this->editMode[$name] = true;  }
    public function disableEditMode(string $name): void { $this->editMode[$name] = false; }
    public function isEnabledEditMode(string $name): bool
    {
        return isset($this->editMode[$name]) ? $this->editMode[$name] : false;
    }

    public static function getShopData(): array { return self::$shopData; }
    public static function setShopData(array $value): void { self::$shopData = $value; }

    public function onEnable(): void
    {
        $this->getLogger()->info("開始中...");
        
        $this->dataFolder = $this->getDataFolder();

        // Resources の保存
        $this->saveResources();
        // 言語読み込み
        $this->loadLanguage();
        // 保存ファイルの読み込み
        $this->loadSaveFiles();

        // イベントの登録
        $this->registerEvents();

        // コマンドの登録
        $this->registerCommands();

        $this->getLogger()->info("開始しました バージョン" . self::VERSION);
    }

    public function onDisable(): void
    {
        $this->shopSaveFile->setAll(self::getShopData());
        $this->shopSaveFile->save();

        $this->getLogger()->info("停止しました");
    }

    /**
     * Resources の保存
     *
     * @return void
     */
    private function saveResources(): void
    {
        // Resource の読み込み
        foreach ($this->getResources() as $resource) {
            $this->saveResource($resource->getFilename(), false);
        }        
    }

    /**
     * 言語の読み込み
     *
     * @return void
     */
    private function loadLanguage(): void
    {
        // Load language
        $langFile = new Config($this->dataFolder . "Language.yml", Config::YAML);
        self::$lang = (array) $langFile->getAll();
    }

    /**
     * 保存ファイルの読み込み
     *
     * @return void
     */
    private function loadSaveFiles(): void
    {
        // SHOP の保存ファイル
        $this->shopSaveFile = new Config($this->dataFolder . "Shops.yml", Config::YAML);
        self::setShopData($this->shopSaveFile->getAll());
    }

    /**
     * イベントを登録
     *
     * @return void
     */
    private function registerEvents(): void
    {
        $pluginManager = $this->getServer()->getPluginManager();
        $pluginManager->registerEvents(new SignTouchEventHandler($this), $this);
        $pluginManager->registerEvents(new SignBreakEventHandler($this), $this);
    }

    /**
     * コマンドの登録
     *
     * @return void
     */
    private function registerCommands(): void
    {
        $cmdMap = $this->getServer()->getCommandMap();
        $cmdMap->register("mshedit", new SetEditModeCommand($this));
    }

    /**
     * Position を文字列変換
     *
     * @param Position $pos
     * @return string
     */
    public function posToString(Position $pos): string
    {
        return round($pos->x) . ':' . round($pos->y) . ':' . round($pos->z) . ':' . $pos->getLevel()->getFolderName();
    }

    /**
     * 言語データベースから指定されたキーの文章を取得する
     * [サードパーティー製プラグインからの呼び出しは非推奨]
     *
     * @param string $key  文章のキー
     * @param array  $input 文章中のシンボルと差し替えるデータ(存在する場合)
     * @return string 文章
     */
    public static function getMessage(string $key, array $input = []): string
    {
        // キーが存在しない場合、エラー文章を返します
        if (!isset(self::$lang[$key])) {
            return TextFormat::RED . "The character string \"" . TextFormat::YELLOW . $key . TextFormat::RED
            . "\" could not be found from the search result database.";
        }
        
        // 改行と色データを適用
        $message = str_replace(["[EOLL]", "[EOL]"], ["\n", "\n" . str_pad(" ", 33)], self::$lang[$key]);
        $message = str_replace(self::$colorTag, self::$color, $message);

        // 文章中のシンボルとインプットされたデータを差し替える
        if (!empty($input)) {
            $count = (int) count($input);
            for ($i = 0; $i < $count; ++$i) {
                $search[] = '[TAG: NO.' . $i . ']';
                $replacement[] = $input[$i];
            }
            $message = str_replace($search, $replacement, $message);
        }

        return $message;
    }

    /**
     * 言語データベース用の色データ
     *
     * @var string
    */
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

    /**
     * 言語データベース用の色データ
     *
     * @var string
    */
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
}
