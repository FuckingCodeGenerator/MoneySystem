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

namespace metowa1227\moneysystem\api\core;

use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\OfflinePlayer;

use metowa1227\moneysystem\Gate;
use metowa1227\moneysystem\core\System;
use metowa1227\moneysystem\logger\OriginalLogger;
use metowa1227\moneysystem\database\SQLiteDataManager;
use metowa1227\moneysystem\event\money\MoneyChangeEvent;
use metowa1227\moneysystem\event\money\MoneySetEvent;
use metowa1227\moneysystem\event\money\MoneyIncreaseEvent;
use metowa1227\moneysystem\event\money\MoneyReduceEvent;

class API implements Gate
{
    const CHANGED_TYPE_INCREASE = 1;
    const CHANGED_TYPE_REDUCE = 2;
    const CHANGED_TYPE_SET = 3;

    public function __construct(System $system)
    {
        $this->system = $system;
        $this->db = new SQLiteDataManager($system);
        $this->lang = new Config($system->getDataFolder() . "LanguageDatabase.yml", Config::YAML);
        $this->user = new Config($system->getDataFolder() . "UserList.yml", Config::YAML);
        $this->config = new Config($system->getDataFolder() . "Config.yml", Config::YAML);
        $this->logger = $system->getLogger();
        $this->lang_all = $this->lang->getAll();
        self::$instance = $this;
    }

    public function close(System $system)
    {
        $this->db->close();
    }

    private static $instance = \null;

    public static function getInstance()
    {
        return self::$instance;
    }

    public function getMessage(string $key, array $input = []) : string
    {
        if (!$this->lang->exists($key))
            return TextFormat::RED . "The character string \"" . TextFormat::YELLOW . $key . TextFormat::RED . "\" could not be found from the search result database.";
        $message = str_replace("[EOL]", "\n" . str_pad(" ", 66), $this->lang_all[$key]);
        $message = str_replace("[EOLL]", "\n", $this->lang_all[$key]);
        $colortag = array("[COLOR: BLACK]", "[COLOR: DARK_BLUE]", "[COLOR: DARK_GREEN]", "[COLOR: DARK_AQUA]", "[COLOR: DARK_RED]", "[COLOR: DARK_PURPLE]", "[COLOR: GOLD]", "[COLOR: GRAY]", "[COLOR: DARK_GRAY]", "[COLOR: BLUE]", "[COLOR: GREEN]", "[COLOR: AQUA]", "[COLOR: RED]", "[COLOR: LIGHT_PURPLE]", "[COLOR: YELLOW]", "[COLOR: WHITE]", "[COLOR: OBFUSCATED]", "[COLOR: BOLD]", "[COLOR: STRIKETHROUGH]", "[COLOR: UNDERLINE]", "[COLOR: ITALIC]", "[COLOR: RESET]");
        $color = array(TextFormat::ESCAPE . "0", TextFormat::ESCAPE . "1", TextFormat::ESCAPE . "2", TextFormat::ESCAPE . "3", TextFormat::ESCAPE . "4", TextFormat::ESCAPE . "5", TextFormat::ESCAPE . "6", TextFormat::ESCAPE . "7", TextFormat::ESCAPE . "8", TextFormat::ESCAPE . "9", TextFormat::ESCAPE . "a", TextFormat::ESCAPE . "b", TextFormat::ESCAPE . "c", TextFormat::ESCAPE . "d", TextFormat::ESCAPE . "e", TextFormat::ESCAPE . "f", TextFormat::ESCAPE . "k", TextFormat::ESCAPE . "l", TextFormat::ESCAPE . "m", TextFormat::ESCAPE . "n", TextFormat::ESCAPE . "o", TextFormat::ESCAPE . "r");
        $message = str_replace($colortag, $color, $message);
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

    public function get($player, bool $array = false)
    {
        $player = $this->getName($player);
        if ($array)
            return $this->db->file("SELECT money FROM account WHERE name = \"$player\"");
        return $this->db->file("SELECT money FROM account WHERE name = \"$player\"")["money"];
    }

    public function getAll(bool $key = false)
    {
        if (!$this->user->getAll())
            return null;
        $result = array();
        if ($key) {
            foreach ($this->user->getAll(true) as $list) {
                array_push($result, $list);
            }
        } else {
            foreach ($this->user->getAll(true) as $list) {
                array_push($result, $this->db->file("SELECT * FROM account WHERE name = \"$list\""));
            }
        }
        return $result;
    }

    public function getUnit() : string
    {
        return $this->config->get("unit");
    }

    public function save() : bool
    {
        return $this->db->save();
    }

    public function set($player, int $money, string $reason = "none", string $by = "unknown") : bool
    {
        $player = $this->getName($player);
        if (!$this->exists($player))
            return false;
        Server::getInstance()->getPluginManager()->callEvent($result = new MoneyChangeEvent($this, $player, $money, $reason, $by, self::CHANGED_TYPE_SET));
        Server::getInstance()->getPluginManager()->callEvent($result2 = new MoneySetEvent($this, $player, $money, $reason, $by));
        if (!$result->isCancelled() && !$result2->isCancelled()) {
            $money = $this->check($money);
            $this->db->file("UPDATE account SET money = $money WHERE name = \"$player\"");
            return true;
        }
        return false;
    }

    public function increase($player, int $money, string $reason = "none", string $by = "unknown") : bool
    {
        $player = $this->getName($player);
        if (!$this->exists($player))
            return false;
        Server::getInstance()->getPluginManager()->callEvent($result = new MoneyChangeEvent($this, $player, $money, $reason, $by, self::CHANGED_TYPE_INCREASE));
        Server::getInstance()->getPluginManager()->callEvent($result2 = new MoneyIncreaseEvent($this, $player, $money, $reason, $by));
        if (!$result->isCancelled() && !$result2->isCancelled()) {
            $money = $this->check($money);
            $money = $this->get($player) + $money;
            if ($money > MAX_MONEY)
                $money = MAX_MONEY;
            $this->db->file("UPDATE account SET money = $money WHERE name = \"$player\"");
            return true;
        }
        return false;
    }

    public function reduce($player, int $money, string $reason = "none", string $by = "unknown") : bool
    {
        $player = $this->getName($player);
        if (!$this->exists($player))
            return false;
        Server::getInstance()->getPluginManager()->callEvent($result = new MoneyChangeEvent($this, $player, $money, $reason, $by, self::CHANGED_TYPE_REDUCE));
        Server::getInstance()->getPluginManager()->callEvent($result2 = new MoneyReduceEvent($this, $player, $money, $reason, $by));
        if (!$result->isCancelled() && !$result2->isCancelled()) {
            $money = $this->check($money);
            $money = $this->get($player) - $money;
            $money = $this->check($money);
            $this->db->file("UPDATE account SET money = $money WHERE name = \"$player\"");
            return true;
        }
        return false;
    }

    public function backup() : bool
    {
        $dir = $this->system->getDataFolder();
        if (!is_dir($dir))
            return false;
        if (!is_dir(Server::getInstance()->getDataPath() . "MoneySystemBackupFiles"))
            @mkdir(Server::getInstance()->getDataPath() . "MoneySystemBackupFiles");
        @mkdir(Server::getInstance()->getDataPath() . "MoneySystemBackupFiles/" . date("D_M_j-H.i.s-T_Y", time()));
        $path = Server::getInstance()->getDataPath() . "MoneySystemBackupFiles/" . date("D_M_j-H.i.s-T_Y", time());
        $file = $path . "\\Account[Backup].sqlite3";
        try {
            if (!copy($dir . "Account.sqlite3", $file))
                throw new \Exception("File backup failed.", 1);
        } catch (\Exception $error) {
            $this->logger->error("File backup failed. To start the server safely please back up the file manually.");
            return false;
        }
        $this->logger->info(TextFormat::GREEN . "The data file was backed up. The data was transferred to another folder.");
        $this->logger->info(TextFormat::GREEN . "Please note that even if the backup succeeds 100% of the data is not protected!");
        return true;
    }

    public function getSettings() : array
    {
        return $this->config->getAll();
    }

    public function getSystemInfo() : array
    {
        return array(PLUGIN_NAME, PLUGIN_VERSION, PLUGIN_CODE);
    }

    public function getDefaultMoney() : int
    {
        return $this->getSettings()["default-money"];
    }

    public function setDefaultMoney(int $money) : bool
    {
        $money = $this->check($money);
        $this->config->set("default-money", $money);
        $this->config->save(true);
        return true;
    }

    public function createAccount($player, int $money = -1) : bool
    {
        $player = $this->getName($player);
        if ($money < 0)
            $money = $this->getDefaultMoney();
        if (!$this->exists($player)) {
            $this->db->file("INSERT OR REPLACE INTO account VALUES (\"$player\", $money, 0, \"NONE\")");
            $this->user->set($player);
            $this->user->save(true);
        }
        return true;
    }

    public function removeAccount($player) : bool
    {
        $player = $this->getName($player);
        if (!$this->exists($player))
            return false;
        $this->db->file("DELETE FROM account WHERE name = \"$player\"");
        $this->user->remove($player);
        $this->user->save(true);
        return true;
    }

    public function exists($player) : bool
    {
        $player = $this->getName($player);
        if (empty($this->db->file("SELECT * FROM account WHERE name = \"$player\"")))
            return false;
        return true;
    }

    public function hasCache($player)
    {
        $player = $this->getName($player);
        return $this->db->file("SELECT cache, by FROM account WHERE name = \"$player\"");
    }

    public function removeCache($player) : bool
    {
        $player = $this->getName($player);
        $backup = $this->hasCache($player);
        $cache = $backup["cache"];
        $by = $backup["by"];
        $this->db->file("UPDATE account SET cache = 0, by = \"NONE\" WHERE name = \"$player\"");
        return true;
    }

    public function addCache($target, $player, $amount) : bool
    {
        $cache = $this->hasCache($target);
        $cache = $cache["cache"];
        $by = ltrim($cache["by"], "NONE");
        $next = $cache + $amount;
        $next2 = $by . ", " . $player;
        $next2 = ltrim($next2, ", ");
        $this->db->file("UPDATE account SET cache = $next, by = \"$next2\" WHERE name = \"$target\"");
        return true;
    }

    private function getName($player) : string
    {
        return is_string($player) ? $player : $player->getName();
    }

    private function check($value) : int
    {
        return $value <= 0 ? 0 : $value;
    }
}
