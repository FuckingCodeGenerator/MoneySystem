<?php
namespace metowa1227\moneysystem;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use metowa1227\moneysystem\api\core\API;
use metowa1227\moneysystem\command\SystemCommand;
use metowa1227\moneysystem\event\player\JoinEvent;
use metowa1227\moneysystem\task\SaveTask;

/**
 * MoneySystem のメインクラスです。
 * 主要な処理はここでします。
 * APIクラスではありません。
 */
class Main extends PluginBase
{
    /**
     * @var integer プラグインのバージョン
     */
    const PLUGIN_VERSION = 13.40;
    /**
     * @var string プラグイン名
     */
    const PLUGIN_NAME = 'MoneySystem';
    /**
     * @var integer 最大所持可能金額
     */
	const MAX_MONEY = 99999999999;

    /**
     * プラグインが有効化された時の処理
     *
     * @return void
     */
    public function onEnable(): void
    {
        $this->getLogger()->info("ようこそMoneySystemへ。");

        // 起動に必要なファイル等を読み込みます
        $this->init();
        // バックアップが有効なら実行します
        $this->backup();

        // イベントを登録します
        $this->initEvent();

        // コマンドを登録します
        $this->registerCommand();
        // プラグインに関する情報をコンソールに表示します
        $this->displayInfoToConsole();
        // 自動セーブのタスクを起動します
        $this->startTask();

        $this->getLogger()->info($this->api->getMessage("system.startup-compleate", array(self::PLUGIN_VERSION)));
    }

    /**
     * プラグインが無効化された時の処理
     *
     * @return void
     */
    public function onDisable(): void
    {
        $this->getLogger()->info("シャットダウンしています...");
        
        // ファイルを保存します
        $this->api->save();
    }

    /**
     * 自動セーブのタスクを起動します
     *
     * @return void
     */
    private function startTask(): void
    {
        if (!$this->config->get("auto-save")) {
            return;
        }
        $this->getScheduler()->scheduleRepeatingTask(
            new SaveTask($this, $this->config->get("save-announce")),
            $this->config->get("save-interval") * 20 * 60);
    }

    /**
     * イベントを登録します
     *
     * @return void
     */
    private function initEvent(): void
    {
        // プレイヤーがサーバーへ参加したときのイベント
        $this->getServer()->getPluginManager()->registerEvents(new JoinEvent(), $this);
    }

    /**
     * データのバックアップをする
     *
     * @return void
     */
    private function backup(): void
    {
        if ($this->config->get("auto-backup")) {
            $this->api->backup();
        } else {
            $this->getLogger()->warning("自動バックアップが無効化されています");
        }     
    }

    /**
     * コンソールへ情報を表示する
     *
     * @return void
    */
    private function displayInfoToConsole(): void
    {
        // Accounts.yml のファイルサイズを算出
        $byte = filesize($this->getDataFolder() . "Accounts.yml");
        $kb = $byte / 1024;
        $mb = number_format($kb / 1024, 2);

        // アカウント数を算出
        if (empty($allData = $this->api->getAll(true))) {
            $count = 0;
        } else {
            $count = count($allData);
        }

        $this->getLogger()->info("セーブデータのファイル情報: Accounts.yml -> " . $byte . "バイト (" . $kb . "KB) (" . $mb . "MB)");
        $this->getLogger()->info($count . " 個のアカウントが使用可能です");
    }

    /**
     * API、セーブデータ、設定ファイル、言語ファイルを読み込む
     *
     * @return void
    */
    private function init(): void
    {
        // 保存ディレクトリが存在しない場合は新規作成します
        $dataPath = $this->getDataFolder();
        if (!is_dir($dataPath)) {
            mkdir($dataPath);
        }

        $this->saveResource("Config.yml", false);
        $this->saveResource("Language.yml", false);
        $this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML);
        $this->lang = new Config($this->getDataFolder() . "Language.yml", Config::YAML);
        $this->api = new API($this);
    }

    /**
     * コマンドマップにコマンドを登録する
     *
     * @return void
    */
    private function registerCommand(): void
    {
        // '/moneysystem' コマンド
        $this->getServer()->getCommandMap()->register("moneysystem", new SystemCommand);
    }

    /**
     * APIを取得する
     *
     * @return self
    */
    public function getAPI(): API
    {
        return $this->api;
    }
}
