<?php
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
