<?php
namespace metowa1227\moneysystem;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use metowa1227\moneysystem\api\core\API;
use metowa1227\moneysystem\command\SystemCommand;
use metowa1227\moneysystem\form\Received;
use metowa1227\moneysystem\event\player\JoinEvent;
use metowa1227\moneysystem\task\SaveTask;

class Main extends PluginBase
{

	const PLUGIN_VERSION = 13.30;
	const PLUGIN_NAME = 'MoneySystem';
	const PLUGIN_CODE = 'xhenom';
	const MAX_MONEY = 99999999999;

    public function onEnable() : void
    {
        $this->getLogger()->info("ようこそMoneySystemへ。");

        $this->init();
        $this->backup();

        $this->getServer()->getPluginManager()->registerEvents(new JoinEvent(), $this);

        $this->registerCommand();
        $this->displayInfoToConsole();
        $this->getScheduler()->scheduleRepeatingTask(new SaveTask($this), $this->config->get("save-interval") * 20 * 60);

        $this->getLogger()->info($this->api->getMessage("system.startup-compleate", array(self::PLUGIN_VERSION)));
    }

    public function onDisable()
    {
        $this->getLogger()->info("シャットダウンしています...");
        $this->api->save();
    }

    /**
     * データのバックアップをする
     *
     * @return void
     */
    private function backup() : void
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
    private function displayInfoToConsole() : void
    {
        $byte = filesize($this->getDataFolder() . "Accounts.yml");
        $kb = $byte / 1024;
        $mb = number_format($kb / 1024, 2);
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
    private function init() : void
    {
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
    private function registerCommand() : void
    {
        $this->getServer()->getCommandMap()->register("moneysystem", new SystemCommand);
    }


    /**
     * APIを取得する
     *
     * @return API
    */
    public function getAPI() : API
    {
        return $this->api;
    }
}
