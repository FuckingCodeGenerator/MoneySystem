<?php
namespace metowa1227\moneysystem\core;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use metowa1227\moneysystem\api\core\API;
use metowa1227\moneysystem\commands\main\SystemCommands;
use metowa1227\moneysystem\commands\player\devices\ExpandingForms;
use metowa1227\moneysystem\form\Received;
use metowa1227\moneysystem\event\player\JoinEvent;
use metowa1227\MoneySystemAPI\MoneySystemAPI;

define("PLUGIN_VERSION", 13.14);
define("PLUGIN_NAME", "MoneySystem");
define("PLUGIN_CODE", "xhenom");
define("MAX_MONEY", 9223372036854775807);

class System extends PluginBase
{
    public function onEnable()
    {
        $this->getLogger()->info("Welcome to MoneySystem");
        $this->getLogger()->info("System file reading and system startup are started.");

        //ファイル準備
        $this->initFiles();
        //API準備
        $this->api = new API($this); //新API
        $old = new MoneySystemAPI($this); //旧API(互換性保持)

        //セーブデータバックアップ
        if ($this->config->get("auto-backup")) {
            $this->api->backup();
        } else {
            $this->getLogger()->warning("The automatic backup function has been disabled.");
            $this->getLogger()->warning("In order to publish the server safely it is necessary to protect the data!");
        }

        //イベント登録
        $this->getServer()->getPluginManager()->registerEvents(new Received($this->getDataFolder()), $this);
        $this->getServer()->getPluginManager()->registerEvents(new JoinEvent(), $this);

        //コマンド準備
        $this->registerCommands();

        //コンソールへ情報表示
        $this->displayInfoToConsole();

        $this->getLogger()->info($this->getMessage("system.startup-compleate", array(PLUGIN_VERSION)));
    }

    public function onDisable()
    {
        $this->getLogger()->info("The database is safely terminated ...");

        //データベースを閉じる
        $this->api->close($this);
        $this->getLogger()->info("Successfully shut down MoneySystem.");
    }

    /**
     * コンソールへデータベースの情報を表示する
     * Display database information to the console
     *
     * @return void
    */
    private function displayInfoToConsole() : void
    {
        //アカウントファイルサイズ取得
        $byte = filesize($this->getDataFolder() . "Account.sqlite3");
        $kb = $byte / 1024;
        $mb = number_format($kb / 1024, 2);
        if (empty($allData = $this->api->getAll(true))) {
            $count = 0;
        } else {
            $count = count($allData);
        }

        //表示
        $this->getLogger()->info("Database information: Account.sqlite3 -> " . $byte . "bytes (" . $kb . "KB) (" . $mb . "MB)");
        $this->getLogger()->info($count . " accounts are online now.");
    }

    /**
     * セーブデータや設定ファイルなどを読み込む
     * Read save data, configuration file, etc.
     *
     * @return void
    */
    private function initFiles() : void
    {
        $dataPath = $this->getDataFolder();
        if (!is_dir($dataPath)) {
            mkdir($dataPath);
        }
        $this->saveResource("FormIDs.yml", false);
        $this->saveResource("Config.yml", false);
        $this->saveResource("LanguageDatabase.yml", false);
        $this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML);
        $this->lang = new Config($this->getDataFolder() . "LanguageDatabase.yml", Config::YAML);
    }

    /**
     * PMMP本体のコマンドマップに本プラグインのコマンドを登録する
     * Register the command of this plugin in the PMMP's command map
     *
     * @return void
    */
    private function registerCommands() : void
    {
        $commandmap = $this->getServer()->getCommandMap();
        $commandmap->register("moneysystem", new SystemCommands($this));
        $commandmap->register("msys", new ExpandingForms($this));
    }

    /**
     * API関数のメッセージを取得する関数の移植
     * 取得したメッセージを返す
     * Porting functions that retrieve API function messages
     * Returns the acquired message
     *
     * @param string $key
     * @param array  $input
     *
     * @return string
    */
    private function getMessage(string $key, array $input = []) : string
    {
        return $this->api->getMessage($key, $input);
    }

    /**
     * APIを取得する
     * Acquire the API
     *
     * @return API
    */
    public function getAPI() : API
    {
        return $this->api;
    }
}
