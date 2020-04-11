<?php

declare(strict_types=1);

namespace metowa1227\msland;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat;
use metowa1227\moneysystem\api\core\API;
use metowa1227\msland\commands\LandParticleCommand;
use metowa1227\msland\event\PlayerLandEditEvent;
use metowa1227\msland\land\LandManager;

class Main extends PluginBase
{
    /** @var Config */
    private $configFile, $langFile, $landFile, $teleportListFile;
    /** @var array */
    private $config;
    /** @var array */
    private static $lang;
    /** @var LandManager */
    private $landManager;
    /** @var self */
    private static $instance;

    public static function getInstance(): self
    {
        return self::$instance;
    }
    
    /**
     * プラグインのデータ保存ディレクトリパス
     *
     * @var string
     */
    private $dirname;

    /**
     * 対象がプレイヤーかどうか
     *
     * @param Player|ConsoleCommandSender $target
     * @return boolean
     */
    public static function isPlayer($target): bool
    {
        return $target instanceof Player;
    }

    /** 
     * @return LandManager
     */
    public function getLandManager(): LandManager
    {
        return $this->landManager;
    }

    /**
     * @return array
     */
    public function getConfigArgs(): array
    {
        return $this->config;
    }

    /**
     * プラグイン読み込み時の処理
     *
     * @return void
     */
    public function onLoad(): void
    {
        self::$instance = $this;

        // プラグインのデータ保存ディレクトリ
        $this->dirname = $this->getDataFolder();

        $this->saveResources();
        $this->initFiles();

        $landData = $this->landFile->getAll();
        $teleportList = $this->teleportListFile->getAll();
        $this->landManager = new LandManager($this, $landData, $teleportList);
    }

    /**
     * プラグイン起動時の処理
     *
     * @return void
     */
    public function onEnable(): void
    {
        $this->updateLandDataFile();
        $this->registerCommands();
        $this->registerEvents();

        $this->getLogger()->info(TextFormat::GREEN . $this->getName() . " を有効化しました");
        $this->getLogger()->notice("注意: 土地管理に" . TextFormat::YELLOW . "Y座標を含める" . TextFormat::RESET . "設定から" . TextFormat::YELLOW . "含めない" . TextFormat::RESET . "設定に" . TextFormat::YELLOW . "戻さないでください");
        $this->getLogger()->notice("一度Y座標を含める設定にすると、Y座標を含めない設定に後から戻すことはできません!");
    }

    /**
     * プラグインが無効化された時の処理
     *
     * @return void
     */
    public function onDisable(): void
    {
        $this->writeData();
    }

    /**
     * データの保存
     *
     * @return void
     */
    private function writeData(): void
    {
        $this->configFile->setAll($this->config);
        $this->landFile->setAll($this->landManager->getLandData());
        $this->teleportListFile->setAll($this->landManager->getAllTeleportList());
        $this->configFile->save();
        $this->landFile->save();
        $this->teleportListFile->save();
    }

    /**
     * イベントの登録
     *
     * @return void
     */
    private function registerEvents(): void
    {
        $this->getServer()->getPluginManager()->registerEvents(new PlayerLandEditEvent, $this);
    }

    /**
     * サーバーのコマンドマップへコマンドを登録
     *
     * @return void
     */
    private $commands = [
        "metowa1227\msland\commands\LandCommand",
        "metowa1227\msland\commands\FirstPosSetCommand",
        "metowa1227\msland\commands\HereCommand",
        "metowa1227\msland\commands\SecondPosSetCommand",
        "metowa1227\msland\commands\TeleportCommand",
    ];
    private function registerCommands(): void
    {
        foreach ($this->commands as $command) {
            $this->getServer()->getCommandMap()->register($this->getName(), new $command($this));
        }

        if ($this->config["enable-landparticle-command"]) {
            // LandParticle コマンド
            $this->getServer()->getCommandMap()->register("landparticle", new LandParticleCommand);
        }
    }

    /**
     * リソースの保存
     *
     * @return void
     */
    private function saveResources(): void
    {
        foreach ($this->getResources() as $resource) {
            $this->saveResource($resource->getFilename(), false);
        }        
    }

    /**
     * 設定ファイルやセーブファイルをロード
     *
     * @return void
     */
    private function initFiles(): void
    {
        $this->configFile = new Config($this->dirname . "Config.yml", Config::YAML);
        $this->langFile   = new Config($this->dirname . "Language.yml", Config::YAML);
        $this->landFile   = new Config($this->dirname . "Lands.yml", Config::YAML);
        $this->teleportListFile = new Config($this->dirname . "TeleportDestination.yml", Config::YAML);

        // ファイルの内容を配列にして保持
        $this->config = $this->configFile->getAll();
        self::$lang   = $this->langFile->getAll();
    }

    /**
     * 土地IDを更新
     *
     * @param integer $id
     * @return void
     */
    public function updateLandId(int $id): void
    {
        $this->config["id"] = $id;
    }

    /**
     * メッセージを取得
     *
     * @param string $key
     * @param array $input
     * @return string
     */
    public static function getMessage(string $key, array $input = []) : string
    {
        return API::getInstance()->getMessage($key, $input, self::$lang);
    }

    /**
     * 座標を整数化する
     *
     * @param Position $position
     * @return Position
     */
    public static function convertFloorPosition(Position $position): Position
    {
        return new Position($position->getFloorX(), $position->getFloorY(), $position->getFloorZ(), $position->getLevel());
    }

    private function updateLandDataFile()
    {
        $updated = 0;
        $lands = $this->landManager->getLandData();
        foreach ($lands as $land) {
            if (!isset($land[LandManager::StartY]) || !isset($land[LandManager::EndY])) {
                $land[LandManager::StartY] = 0;
                $land[LandManager::EndY] = Level::Y_MAX;
                $lands[$land[LandManager::ID]] = $land;
                $updated++;
            }
        }

        $this->landManager->updateLandData($this, $lands);
        $this->getLogger()->info("Updated " . $updated . " lands data");
    }
}
