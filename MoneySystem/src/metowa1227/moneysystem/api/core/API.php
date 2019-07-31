<?php
namespace metowa1227\moneysystem\api\core;

use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\OfflinePlayer;
use pocketmine\Server;
use pocketmine\Player;
use metowa1227\moneysystem\api\listener\Listener;
use metowa1227\moneysystem\api\listener\Types;
use metowa1227\moneysystem\Main;
use metowa1227\moneysystem\api\processor\GetName;
use metowa1227\moneysystem\api\processor\Check;
use metowa1227\moneysystem\event\money\MoneyChangeEvent;
use metowa1227\moneysystem\event\money\MoneyIncreaseEvent;
use metowa1227\moneysystem\event\money\MoneyReduceEvent;
use metowa1227\moneysystem\event\money\MoneySetEvent;

class API implements Listener, Types
{
    use GetName, Check;

    /**
     * 言語データベース用の色データ
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
     *
     * @var string
    */
    private $color = [
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

    /** @var API */
    private static $instance = null;
    /** @var Config */
    private $dataFile, $lang, $user, $config, $logger;
    /** @var array */
    private $lang_all;
    /** @var array */
    private $data = null;

    public function __construct(Main $system)
    {
        $this->system = $system;
        $this->dataFile = new Config($system->getDataFolder() . "Accounts.yml", Config::YAML);
        $this->data = $this->dataFile->getAll();
        $this->lang = new Config($system->getDataFolder() . "Language.yml", Config::YAML);
        $this->user = new Config($system->getDataFolder() . "UserList.yml", Config::YAML);
        $this->config = new Config($system->getDataFolder() . "Config.yml", Config::YAML);
        $this->logger = $system->getLogger();
        $this->lang_all = $this->lang->getAll();
        self::$instance = $this;
    }

    /**
     * APIのインスタンスを取得する
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
        $message = str_replace(["[EOLL]", "[EOL]"], ["\n", "\n" . str_pad(" ", 33)], $this->lang_all[$key]);
        $message = str_replace($this->colorTag, $this->color, $message);
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
     *
     * @param string | Player  $player
     * @param bool             $array  [アカウントごと返すか]
     *
     * @return null | int | array
    */
    public function get($player, bool $array = false)
    {
        $this->getName($player);
        if (!$this->exists($player)) {
            return null;
        }
        return $array ? $this->data[$player] : $this->data[$player];
    }

    /**
     * 全プレイヤーの所持金を取得する
     *
     * @param bool $key [プレイヤー名のみ返すか]
     *
     * @return array | null     配列: [プレイヤー名, 所持金]
    */
    public function getAll(bool $key = false) : ?array
    {
        $this->getName($player);
        if ($key) {
            return array_keys($this->data);
        }

        $result = [];
        foreach ($this->data as $player => $data) {
            $result[$player] = $data;
        }
        return $result;
    }

    /**
     * 通貨を取得
     *
     * @return string
    */
    public function getUnit() : string
    {
        return $this->config->get("unit");
    }

    /**
     * データを保存する
     *
     * @return bool
    */
    public function save() : bool
    {
        $this->dataFile->setAll($this->data);
        return $this->dataFile->save();
    }

    /**
     * @param Player | string | array  $player
     * @param int                      $money
     * @param string                   $reason
     * @param string                   $by [caller]
     *
     * @return void
     */
    private function processArray($players, $money, $reason, $by, $type) : void
    {
        foreach ($players as $player) {
            switch ($type) {
                case self::TYPE_INCREASE:
                    $this->increase($player, $money, $by, $reason);
                    break;
                case self::TYPE_REDUCE:
                    $this->reduce($player, $money, $by, $reason);
                    break;
                case self::TYPE_SET:
                    $this->set($player, $money, $by, $reason);
                    break;
            }
        }
    }

    /**
     * プレイヤーの所持金を設定する
     *
     * @param Player | string | array  $player
     * @param int                      $money
     * @param string                   $by [caller]
     * @param string                   $reason
     *
     * @return bool
    */
    public function set($player, int $money, string $by = "unknown", string $reason = "none") : bool
    {
        if (is_array($player)) {
            $this->processArray($player, $money, $reason, $by, self::TYPE_SET);
        } else {
            $this->getName($player);
            if (!$this->exists($player)) {
                return false;
            }
            Server::getInstance()->getPluginManager()->callEvent($result = new MoneyChangeEvent($player, $money, $reason, $by, self::TYPE_SET, $this->get($player)));
            Server::getInstance()->getPluginManager()->callEvent($result2 = new MoneySetEvent($player, $money, $reason, $by, $this->get($player)));
            if (!$result->isCancelled() && !$result2->isCancelled()) {
                $money = $this->check($money);
                $this->data[$player] = $money;
                return true;
            }
            return false;
        }
    }

    /**
     * プレイヤーの所持金を増やす
     *
     * @param Player | string  $player
     * @param int              $money
     * @param string           $reason
     * @param string           $by [caller]
     *
     * @return bool
    */
    public function increase($player, int $money, string $by = "unknown", string $reason = "none") : bool
    {
        if (is_array($player)) {
            $this->processArray($player, $money, $reason, $by, self::TYPE_INCREASE);
        } else {
            $this->getName($player);
            if (!$this->exists($player)) {
                return false;
            }
            Server::getInstance()->getPluginManager()->callEvent($result = new MoneyChangeEvent($player, $money, $reason, $by, self::TYPE_INCREASE, $this->get($player)));
            Server::getInstance()->getPluginManager()->callEvent($result2 = new MoneyIncreaseEvent($player, $money, $reason, $by, $this->get($player)));
            if (!$result->isCancelled() && !$result2->isCancelled()) {
                $money = $this->get($player) + $money;
                if ($money > Main::MAX_MONEY) {
                    $money = Main::MAX_MONEY;
                }
                $money = $this->check($money);
                $this->data[$player] = $money;
                return true;
            }
            return false;
        }
    }

    /**
     * プレイヤーの所持金を減らす
     *
     * @param Player | string  $player
     * @param int              $money
     * @param string           $reason
     * @param string           $by [caller]
     *
     * @return bool
    */
    public function reduce($player, int $money, string $by = "unknown", string $reason = "none") : bool
    {
        if (is_array($player)) {
            $this->processArray($player, $money, $reason, $by, self::TYPE_REDUCE);
        } else {
            $this->getName($player);
            if (!$this->exists($player)) {
                return false;
            }
            Server::getInstance()->getPluginManager()->callEvent($result = new MoneyChangeEvent($player, $money, $reason, $by, self::TYPE_REDUCE, $this->get($player)));
            Server::getInstance()->getPluginManager()->callEvent($result2 = new MoneyReduceEvent($player, $money, $reason, $by, $this->get($player)));
            if (!$result->isCancelled() && !$result2->isCancelled()) {
                $money = $this->get($player) - $money;
                $money = $this->check($money);
                $this->data[$player] = $money;
                return true;
            }
            return false;
        }
    }

    /**
     * データをバックアップする
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
        $file = $path . "\\Accounts[Backup].yml";
        try {
            if (!copy($dir . "Accounts.yml", $file)) {
                throw new \Exception("File backup failed.");
            }
        } catch (\Exception $error) {
            $this->logger->error($this->getMessage("backup-failed"));
            return false;
        }
        $this->logger->info($this->getMessage("backup-success"));
        return true;
    }

    /**
     * 設定内容を取得する
     *
     * @return array
    */
    public function getSettings() : array
    {
        return $this->config->getAll();
    }

    /**
     * MoneySystemの情報を取得する
     *
     * @return float
    */
    public function getVersion() : float
    {
        return Main::PLUGIN_VERSION;
    }

    /**
     * デフォルトの所持金を取得する
     *
     * @return int
    */
    public function getDefaultMoney() : int
    {
        return $this->getSettings()["default-money"];
    }

    /**
     * デフォルトの所持金を設定する
     *
     * @param int $money
     *
     * @return bool
    */
    public function setDefaultMoney(int $money) : bool
    {
        $money = $this->check($money);
        $this->config->set("default-money", $money);
        $this->config->save();
        return true;
    }

    /**
     * アカウントを作成する
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
            $this->data[$player] = $money;
        }
        return true;
    }

    /**
     * アカウントを削除する
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
        unset($this->data[$player]);
        return true;
    }

    /**
     * プレイヤーのアカウントが存在するかを調べる
     *
     * @param Player | string  $player
     * 
     * @return bool
    */
    public function exists($player) : bool
    {
        $this->getName($player);
        return isset($this->data[$player]);
    }
}
