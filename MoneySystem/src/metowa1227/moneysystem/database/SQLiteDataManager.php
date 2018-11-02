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

namespace metowa1227\moneysystem\database;

use pocketmine\utils\Config;

use metowa1227\moneysystem\core\System;
use metowa1227\moneysystem\api\core\API;

class SQLiteDataManager
{
    public function __construct(System $main)
    {
        $this->db = new \SQLite3($main->getDataFolder() . "Account.sqlite3", SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $this->config = new Config($main->getDataFolder() . "Config.yml", Config::YAML);
        $this->lang = new Config($main->getDataFolder() . "LanguageDatabase.yml", Config::YAML);
        $this->user = new Config($main->getDataFolder() . "UserList.yml", Config::YAML);
        $this->dir = $main->getDataFolder();
        $this->file("CREATE TABLE IF NOT EXISTS account (name TEXT PRIMARY KEY, money INT, cache INT, by TEXT)");
    }

    public function file($inst)
    {
        if ($this->config->get("file-log")) {
            $debug = debug_backtrace();
            $function = $debug[1]["function"];
            $line = $debug[1]["line"];
            $file = $debug[1]["file"];
            $class = $debug[1]["class"];
            $message = "File access log:\n    " . date('H:i:s') . "\n    - ファイル名: " . $file . "\n    - 関数名: " . $function . " (クラス名: " . $class . " | " . $line . "行目)\n    からMoneySystemのアカウントデータファイルにアクセスがありました。\n";
            if (!file_exists($this->dir . "AccessLog(BackLog).log"))
                @touch($this->dir . "AccessLog(BackLog).log");
            $file = @file_get_contents($this->dir . "AccessLog(BackLog).log");
            $file .= $message;
            @file_put_contents($this->dir . "AccessLog(BackLog).log", $file);
        }
        return $this->db->query($inst)->fetchArray(SQLITE3_ASSOC);
    }

    public function save() : bool
    {
        $this->config->save(true);
        $this->user->save(true);
        $this->lang->save(true);
        return true;

    }

    public function close()
    {
        $this->file("VACUUM");
        $this->db->close();
    }
}
