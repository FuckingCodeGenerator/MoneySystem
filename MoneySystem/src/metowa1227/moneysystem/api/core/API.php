<?php
namespace metowa1227\moneysystem\api\core;

use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\OfflinePlayer;
use pocketmine\{
    Server,
    Player
};
use metowa1227\moneysystem\api\listener\{
    Listener,
    Types
};
use metowa1227\moneysystem\core\System;
use metowa1227\moneysystem\api\processor\{
    Processor,
    GetName,
    Check
};
use metowa1227\moneysystem\database\SQLiteDataManager;

class API extends Processor implements Listener, Types
{
    use GetName, Check;
    /**
     * 言語データベース用の色データ
     * Color conversion data of language file
     *
     * @var string
    */
    private $colorTag = [
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
     * Color conversion data of language file
     *
     * @var string
    */
    private $color = [
        TextFormat::ESCAPE . "0",
        TextFormat::ESCAPE . "1",
        TextFormat::ESCAPE . "2",
        TextFormat::ESCAPE . "3",
        TextFormat::ESCAPE . "4",
        TextFormat::ESCAPE . "5",
        TextFormat::ESCAPE . "6",
        TextFormat::ESCAPE . "7",
        TextFormat::ESCAPE . "8",
        TextFormat::ESCAPE . "9",
        TextFormat::ESCAPE . "a",
        TextFormat::ESCAPE . "b", 
        TextFormat::ESCAPE . "c",
        TextFormat::ESCAPE . "d",
        TextFormat::ESCAPE . "e",
        TextFormat::ESCAPE . "f",
        TextFormat::ESCAPE . "k",
        TextFormat::ESCAPE . "l",
        TextFormat::ESCAPE . "m",
        TextFormat::ESCAPE . "n",
        TextFormat::ESCAPE . "o",
        TextFormat::ESCAPE . "r"
    ];

    /**
     * @var API
    */
    private static $instance = null;

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

    public function close(System $system) : void
    {
        $this->db->close();
    }

    /**
     * APIのインスタンスを取得する
     * Get an instance of the API
     *
     * @return API
    */
    public static function getInstance() : self
    {
        return self::$instance;
    }

    public function getMessage(string $key, array $input = []) : string
    {
        if (!$this->lang->exists($key)) {
            return TextFormat::RED . "The character string \"" . TextFormat::YELLOW . $key . TextFormat::RED . "\" could not be found from the search result database.";
        }
        $message = str_replace("[EOL]", "\n" . str_pad(" ", 66), $this->lang_all[$key]);
        $message = str_replace("[EOLL]", "\n", $this->lang_all[$key]);
        $colorTag = $this->colorTag;
        $color = $this->color;
        $message = str_replace($colorTag, $color, $message);
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

    /**
     * プレイヤーの所持金を取得する
     * Get the player's money
     *
     * @param string | Player  $player
     * @param bool             $array  [trueの場合はアカウントごと返す]
     *
     * @return null | int
    */
    public function get($player, bool $array = false)
    {
        $this->getName($player);
        if ($array) {
            return $this->db->file("SELECT money FROM account WHERE name = \"$player\"");
        }
        return $this->db->file("SELECT money FROM account WHERE name = \"$player\"")["money"];
    }

    /**
     * 全プレイヤーの所持金を取得する
     * Get all player's money
     *
     * @param bool $key [trueの場合はアカウントごと返す]
     *
     * @return array
    */
    public function getAll(bool $key = false) : array
    {
        if (!$this->user->getAll()) {
            return [];
        }

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

    /**
     * 通貨を取得
     * Get currency
     *
     * @return string
    */
    public function getUnit() : string
    {
        return $this->config->get("unit");
    }

    /**
     * データを保存する
     * Data save
     *
     * @return bool
    */
    public function save() : bool
    {
        return $this->db->save();
    }

    /**
     * @param Player | string | array  $player
     * @param int                      $money
     * @param string                   $reason
     * @param string                   $by [caller]
     *
     * @return bool
     */
    private function processEdit($player, $money, $reason, $by, $type) : bool
    {
        if (!is_array($player)) {
            return $this->process($player, $money, $reason, $by, $type, $this->db);
        }

        foreach ($player as $players) {
            if (!$this->process($players, $money, $reason, $by, $type, $this->db)) {
                return false;
            }
        }
        return true;
    }

    /**
     * プレイヤーの所持金を設定する
     * Set player's money
     *
     * @param Player | string | array  $player
     * @param int                      $money
     * @param string                   $reason
     * @param string                   $by [caller]
     *
     * @return bool
    */
    public function set($player, int $money, string $reason = "none", string $by = "unknown") : bool
    {
        return $this->processEdit($player, $money, $reason, $by, self::TYPE_SET);
    }

    /**
     * プレイヤーの所持金を増やす
     * Increase player's money
     *
     * @param Player | string  $player
     * @param int              $money
     * @param string           $reason
     * @param string           $by [caller]
     *
     * @return bool
    */
    public function increase($player, int $money, string $reason = "none", string $by = "unknown") : bool
    {
        return $this->processEdit($player, $money, $reason, $by, self::TYPE_INCREASE);
    }

    /**
     * プレイヤーの所持金を減らす
     * Reduce player's money
     *
     * @param Player | string  $player
     * @param int              $money
     * @param string           $reason
     * @param string           $by [caller]
     *
     * @return bool
    */
    public function reduce($player, int $money, string $reason = "none", string $by = "unknown") : bool
    {
        return $this->processEdit($player, $money, $reason, $by, self::TYPE_REDUCE);
    }

    /**
     * データをバックアップする
     * Back up data
     *
     * @return bool
    */
    public function backup() : bool
    {
        $dir = $this->system->getDataFolder();
        if (!is_dir($dir)) {
            return false;
        }
        if (!is_dir(Server::getInstance()->getDataPath() . "MoneySystemBackupFiles")) {
            @mkdir(Server::getInstance()->getDataPath() . "MoneySystemBackupFiles");
        }
        @mkdir(Server::getInstance()->getDataPath() . "MoneySystemBackupFiles/" . date("D_M_j-H.i.s-T_Y", time()));
        $path = Server::getInstance()->getDataPath() . "MoneySystemBackupFiles/" . date("D_M_j-H.i.s-T_Y", time());
        $file = $path . "\\Account[Backup].sqlite3";
        try {
            if (!copy($dir . "Account.sqlite3", $file)) {
                throw new \Exception("File backup failed.");
            }
        } catch (\Exception $error) {
            $this->logger->error("File backup failed. To start the server safely please back up the file manually.");
            return false;
        }
        $this->logger->info(TextFormat::GREEN . "The data file was backed up. The data was transferred to another folder.");
        $this->logger->info(TextFormat::GREEN . "Please note that even if the backup succeeds 100% of the data is not protected!");
        return true;
    }

    /**
     * 設定内容を取得する
     * Acquire setting contents
     *
     * @return array
    */
    public function getSettings() : array
    {
        return $this->config->getAll();
    }

    /**
     * MoneySystemの情報を取得する
     * Get the MoneySystem's information
     *
     * @return array
    */
    public function getSystemInfo() : array
    {
        return array(PLUGIN_NAME, PLUGIN_VERSION, PLUGIN_CODE);
    }

    /**
     * デフォルトの所持金を取得する
     * Get the default money
     *
     * @return int
    */
    public function getDefaultMoney() : int
    {
        return $this->getSettings()["default-money"];
    }

    /**
     * デフォルトの所持金を設定する
     * Set default default money
     *
     * @param int $money
     *
     * @return bool
    */
    public function setDefaultMoney(int $money) : bool
    {
        $money = $this->check($money);
        $this->config->set("default-money", $money);
        $this->config->save(true);
        return true;
    }

    /**
     * アカウントを作成する
     * Create an account
     *
     * @param Player | string  $player
     * @param int              $money
     *
     * @return bool
    */
    public function createAccount($player, int $money = -1) : bool
    {
        $this->getName($player);
        if ($money < 0) {
            $money = $this->getDefaultMoney();
        }
        if (!$this->exists($player)) {
            $this->db->file("INSERT OR REPLACE INTO account VALUES (\"$player\", $money, 0, \"NONE\")");
            $this->user->set($player);
            $this->user->save(true);
        }
        return true;
    }

    /**
     * アカウントを削除する
     * Remove an account
     *
     * @param Player | string  $player
     *
     * @return bool
    */
    public function removeAccount($player) : bool
    {
        $this->getName($player);
        if (!$this->exists($player)) {
            return false;
        }
        $this->db->file("DELETE FROM account WHERE name = \"$player\"");
        $this->user->remove($player);
        $this->user->save(true);
        return true;
    }

    /**
     * プレイヤーのアカウントが存在するかを調べる
     * Check if player's account exists
     *
     * @param Player | string  $player
     * 
     * @return bool
    */
    public function exists($player) : bool
    {
        $this->getName($player);
        return $this->user->exists($player);
    }

    public function hasCache($player)
    {
        $this->getName($player);
        return $this->db->file("SELECT cache, by FROM account WHERE name = \"$player\"");
    }

    public function removeCache($player) : bool
    {
        $this->getName($player);
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
}
