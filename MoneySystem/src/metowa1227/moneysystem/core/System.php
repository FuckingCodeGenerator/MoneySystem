<?php

/*
*  __  __       _                             __    ___    ___   _______
* |  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
* | |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
* | |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
* |_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
* All this program is made by hand of metowa1227.
* I certify here that all authorities are in metowa1227.
* Expiration date of certification: unlimited
* Secondary distribution etc are prohibited.
* The update is also done by the developer.
* This plugin is a developer API plugin to make it easier to write code.
* When using this plug-in, be sure to specify it somewhere.
* Warning if violation is confirmed.
*
* Developer: metowa1227
*/

/*
    Plugin description

    - CONTENTS
        - Lightweight, fast and multifunctional economic system.

    - AUTHOR
        - metowa1227 (MoneySystemAPI)

    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Pro 64bit
        - Intel(R) Core 2 Duo(TM) E8400 @ 3.00GHz
        - 8192MB DDR2 SDRAM PC2-5300(667MHz) , PC2-6400(800MHz)
        - Altay 3.0.6+dev for Minecraft: PE v1.5.0 (protocol version 274)
        - PHP 7.2.1 64bit supported version
*/

namespace metowa1227\moneysystem\core;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use metowa1227\moneysystem\api\core\API;
use metowa1227\moneysystem\commands\main\SystemCommands;
use metowa1227\moneysystem\commands\player\devices\ExpandingForms;
use metowa1227\moneysystem\task\update\Update_Async as Update;
use metowa1227\moneysystem\form\Received;
use metowa1227\moneysystem\event\player\JoinEvent;

use metowa1227\MoneySystemAPI\MoneySystemAPI;

define("PLUGIN_VERSION", 13.13);
define("PLUGIN_NAME", "MoneySystem");
define("PLUGIN_CODE", "xhenom");
define("MAX_MONEY", 9223372036854775807);

class System extends PluginBase
{
    public function onEnable()
    {
        $this->getLogger()->info("W e l c o m e  t o  M o n e y S y s t e m");
        $this->getLogger()->info("System file reading and system startup are started.");
        @mkdir($this->getDataFolder(), 0777, true);
        $this->saveResource("FormIDs.yml". false);
        $this->saveResource("Config.yml". false);
        $this->saveResource("LanguageDatabase.yml". false);
        $this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML);
        $this->lang = new Config($this->getDataFolder() . "LanguageDatabase.yml", Config::YAML);
        $this->preparing();
        $this->getLogger()->info($this->getMessage("system.startup-compleate", array(PLUGIN_VERSION)));
    }

    public function onDisable()
    {
        $this->getLogger()->info("The database is safely terminated ...");
        $this->api->close($this);
        $this->getLogger()->info("Successfully shut down MoneySystem.");
    }

    private function preparing()
    {
        $this->api = new API($this);
        $old = new MoneySystemAPI($this);
        if ($this->config->get("auto-updatecheck"))
            $this->getServer()->getAsyncPool()->submitTask(new Update($this->getDataFolder(), $this->lang->getAll(), $this->getLogger()));
        else
            $this->getLogger()->warning($this->getMessage("system.update-disabled"));
        if ($this->config->get("auto-backup")) {
            $this->api->backup();
        } else {
            $this->getLogger()->warning("The automatic backup function has been disabled.");
            $this->getLogger()->warning("In order to publish the server safely it is necessary to protect the data!");
        }
        $this->getServer()->getPluginManager()->registerEvents(new Received($this->getDataFolder()), $this);
        $this->getServer()->getPluginManager()->registerEvents(new JoinEvent(), $this);
        $this->registerCommands();
        $byte = filesize($this->getDataFolder() . "Account.sqlite3");
        $kb = $byte / 1024;
        $mb = number_format($kb / 1024, 2);
        $count = count($this->api->getAll(true));
        if (empty($count))
            $count = 0;
        $this->getLogger()->info("Database information: Account.sqlite3 -> " . $byte . "bytes (" . $kb . "KB) (" . $mb . "MB)");
        $this->getLogger()->info($count . " accounts are online now.");
    }

    private function registerCommands()
    {
        $commandmap = $this->getServer()->getCommandMap();
        $commandmap->register("moneysystem", new SystemCommands($this));
        $commandmap->register("msys", new ExpandingForms($this));
    }

    private function getMessage(string $key, array $input = []) : string
    {
        return $this->api->getMessage($key, $input);
    }

    public function getAPI() : API
    {
        return $this->api;
    }
}
