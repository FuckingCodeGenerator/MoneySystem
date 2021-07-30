<?php
namespace metowa1227\moneysystem\api\core;

use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\Player;
use metowa1227\moneysystem\Main;
use metowa1227\moneysystem\api\MoneySystemAPI;
use metowa1227\moneysystem\api\traits\GetNameTrait;
use metowa1227\moneysystem\api\traits\CheckMoneyTrait;
use metowa1227\moneysystem\event\money\MoneyChangeEvent;
use metowa1227\moneysystem\event\money\MoneyIncreaseEvent;
use metowa1227\moneysystem\event\money\MoneyReduceEvent;
use metowa1227\moneysystem\event\money\MoneySetEvent;

/**
 * MoneySystem のAPIクラスです
 * プラグイン類はこのクラスへアクセスします
 */
class API implements MoneySystemAPI
{
    use GetNameTrait, CheckMoneyTrait;

    /** @var API */
    private static $instance = null;
    
    /** @var \AttachableThreadedLogger */
    private $logger;

    /** @var Main */
    private $mainSystem;

    /**
     * $dataFile アカウントの保存ファイル
     * $lang     言語ファイル
     * $config   設定ファイル
     *
     * @var Config
     */
    private $dataFile, $lang, $config;

    /**
     * 言語を配列に格納したもの
     *
     * @var array [LangKey => Text]
     */
    private $langArray;

    /**
     * アカウントを配列に格納したもの
     *
     * @var array [PlayerName => Money]
     */
    private $accountDataArray = null;

    /**
     * API のコンストラクタ
     *
     * @param Main $mainSystem
     */
    public function __construct(Main $mainSystem)
    {
        $this->mainSystem = $mainSystem;

        // ファイルを読み込む
        $this->dataFile = new Config($mainSystem->getDataFolder() . "Accounts.yml", Config::YAML);
        $this->lang     = new Config($mainSystem->getDataFolder() . "Language.yml", Config::YAML);
        $this->config   = new Config($mainSystem->getDataFolder() . "Config.yml", Config::YAML);

        // ファイルの内容を配列に格納する
        $this->logger = $mainSystem->getLogger();
        $this->accountDataArray = $this->dataFile->getAll();
        $this->langArray = $this->lang->getAll();

        // CONSOLE の所持金を初期化
        if (!$this->createAccount("CONSOLE", Main::MAX_MONEY)) {
            $this->set("CONSOLE", Main::MAX_MONEY);
        }

        self::$instance = $this;
    }

    /**
     * 言語データベースから指定されたキーの文章を取得する
     *
     * @param string $key  文章のキー
     * @param array  $input 文章中のシンボルと差し替えるデータ(存在する場合)
     * @param array  $langDataBase 言語データベース
     * @return string 文章
     */
    public function getMessage(string $key, array $input = [], array $langDataBase = []): string
    {
        if (count($langDataBase) <= 0) {
            $langDataBase = $this->langArray;
        }

        // キーが存在しない場合、エラー文章を返します
        if (!isset($langDataBase[$key])) {
            return TextFormat::RED . "[MoneySystem] The character string \"" . TextFormat::YELLOW . $key . TextFormat::RED . "\" could not be found from the search result database.";
        }
        
        // 改行と色データを適用
        $message = str_replace(["[EOLL]", "[EOL]"], ["\n", "\n" . str_pad(" ", 33)], $langDataBase[$key]);
        $message = str_replace(self::colorTag, self::color, $message);

        // 文章中のシンボルとインプットされたデータを差し替える
        if (!empty($input)) {
            $count = (int) count($input);
            for ($i = 0; $i < $count; ++$i) {
                $search[] = '[TAG: NO.' . $i . ']';
                $replacement[] = $input[$i];
            }
            $message = str_replace($search, $replacement, $message);
        }

        return $message;
    }

    public function __get($player): ?int
    {
        return $this->get($player);
    }

    public function __set($player, $value)
    {
        $this->set($player, $value);
    }

    /**
     * APIのインスタンスを取得する
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * プレイヤーの所持金を取得する
     *
     * @param string|Player $player
     * @return null|int
     */
    public function get($player): ?int
    {
        $this->getName($player);
        if (!$this->exists($player)) {
            return null;
        }
        return $this->accountDataArray[$player];
    }

    /**
     * 全プレイヤーの所持金を取得する
     *
     * @param boolean $key プレイヤー名のみを返します
     * @return array|null
     */
    public function getAll(bool $key = false): ?array
    {
        // GetNameTrait
        $this->getName($player);

        $result = $this->accountDataArray;
        unset($result["CONSOLE"]);

        // プレイヤー名のみ返す
        if ($key) {
            return array_keys($result);
        }

        return $result;
    }

    /**
     * お金の通貨を取得する
     *
     * @return string
     */
    public function getUnit(): string
    {
        return $this->config->get("unit");
    }

    /**
     * データを保存する
     *
     * @return bool
     */
    public function save(): bool
    {
        $this->dataFile->setAll($this->accountDataArray);
        return $this->dataFile->save();
    }

    /**
     * 所持金操作のプレイヤー引数に配列が渡された場合の処理
     *
     * @param array   $players
     * @param integer $money
     * @param string  $reason
     * @param string  $calledBy
     * @param integer $type
     * @return void
     */
    private function processArray(array $players, int $money, string $reason, string $calledBy, int $type): void
    {
        foreach ($players as $player) {
            switch ($type) {
                case self::TYPE_INCREASE: $this->increase($player, $money, $calledBy, $reason); break;
                case self::TYPE_REDUCE: $this->reduce($player, $money, $calledBy, $reason); break;
                case self::TYPE_SET: $this->set($player, $money, $calledBy, $reason); break;
            }
        }
    }

    /**
     * プレイヤーの所持金を設定する
     *
     * @param string|array|Player $player
     * @param integer $money
     * @param string  $calledBy
     * @param string  $reason
     * @return boolean
     */
    public function set($player, int $money, string $calledBy = "unknown", string $reason = "none"): bool
    {
        // プレイヤー引数が配列だったら
        if (is_array($player)) {
            $this->processArray($player, $money, $reason, $calledBy, self::TYPE_SET);
        } else {
            // GetNameTrait
            $this->getName($player);

            if ($player === "CONSOLE") {
                return true;
            }

            // アカウントが存在しない場合
            if (!$this->exists($player)) {
                return false;
            }

            // MoneyChangeEventとMoneySetEventをコールする
            $changeEvent = new MoneyChangeEvent($player, $money, $reason, $calledBy, self::TYPE_SET, $this->get($player));
            $setEvent = new MoneySetEvent($player, $money, $reason, $calledBy, $this->get($player));
            Server::getInstance()->getPluginManager()->callEvent($changeEvent);
            Server::getInstance()->getPluginManager()->callEvent($setEvent);
            
            // 2つのイベントがキャンセルされなければ操作
            if (!$changeEvent->isCancelled() && !$setEvent->isCancelled()) {
                // CheckMoneyTrait
                $money = $this->check($money);
                $this->accountDataArray[$player] = $money;
                return true;
            }

            return false;
        }
    }

    /**
     * プレイヤーの所持金を増やす
     *
     * @param string|array|Player $player
     * @param integer $money
     * @param string  $calledBy
     * @param string  $reason
     * @return boolean
     */
    public function increase($player, int $money, string $calledBy = "unknown", string $reason = "none"): bool
    {
        // プレイヤー引数が配列だったら
        if (is_array($player)) {
            $this->processArray($player, $money, $reason, $calledBy, self::TYPE_INCREASE);
        } else {
            // GetNameTrait
            $this->getName($player);

            if ($player === "CONSOLE") {
                return true;
            }

            // アカウントが存在しない場合
            if (!$this->exists($player)) {
                return false;
            }

            // MoneyChangeEventとMoneyIncreaseEventをコールする
            $changeEvent = new MoneyChangeEvent($player, $money, $reason, $calledBy, self::TYPE_INCREASE, $this->get($player));
            $increaseEvent = new MoneyIncreaseEvent($player, $money, $reason, $calledBy, $this->get($player));
            Server::getInstance()->getPluginManager()->callEvent($changeEvent);
            Server::getInstance()->getPluginManager()->callEvent($increaseEvent);

            // 2つのイベントがキャンセルされなければ操作
            if (!$changeEvent->isCancelled() && !$increaseEvent->isCancelled()) {
                $money = $this->get($player) + $money;
                if ($money > Main::MAX_MONEY) {
                    $money = Main::MAX_MONEY;
                }
                // CheckMoneyTrait
                $money = $this->check($money);
                $this->accountDataArray[$player] = $money;
                return true;
            }

            return false;
        }
    }

    /**
     * プレイヤーの所持金を減らす
     *
     * @param string|array|Player $player
     * @param integer $money
     * @param string  $calledBy
     * @param string  $reason
     * @return boolean
     */
    public function reduce($player, int $money, string $calledBy = "unknown", string $reason = "none"): bool
    {
        // プレイヤー引数が配列だったら
        if (is_array($player)) {
            $this->processArray($player, $money, $reason, $calledBy, self::TYPE_REDUCE);
        } else {
            // GetNameTrait
            $this->getName($player);

            if ($player === "CONSOLE") {
                return true;
            }

            // アカウントが存在しない場合
            if (!$this->exists($player)) {
                return false;
            }
            
            // MoneyChangeEventとMoneyReduceEventをコールする
            $changeEvent = new MoneyChangeEvent($player, $money, $reason, $calledBy, self::TYPE_REDUCE, $this->get($player));
            $reduceEvent = new MoneyReduceEvent($player, $money, $reason, $calledBy, $this->get($player));
            Server::getInstance()->getPluginManager()->callEvent($changeEvent);
            Server::getInstance()->getPluginManager()->callEvent($reduceEvent);

            // 2つのイベントがキャンセルされなければ操作
            if (!$changeEvent->isCancelled() && !$reduceEvent->isCancelled()) {
                $money = $this->get($player) - $money;
                // CheckMoneyTrait
                $money = $this->check($money);
                $this->accountDataArray[$player] = $money;
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
    public function backup(): bool
    {
        $dir = $this->mainSystem->getDataFolder();

        // 保存ディレクトリが存在しない場合新規作成
        if (!is_dir(Server::getInstance()->getDataPath() . "MoneySystemBackupFiles")) {
            @mkdir(Server::getInstance()->getDataPath() . "MoneySystemBackupFiles");
        }

        // 保存先フォルダを新規作成
        @mkdir(Server::getInstance()->getDataPath() . "MoneySystemBackupFiles/" . date("D_M_j-H.i.s-T_Y", time()));
        $path = Server::getInstance()->getDataPath() . "MoneySystemBackupFiles/" . date("D_M_j-H.i.s-T_Y", time());

        // ファイルのコピーを実行
        $file = $path . "\\Accounts[Backup].yml";
        try {
            if (!copy($dir . "Accounts.yml", $file)) {
                throw new \Exception("File backup failed.");
            }
        } catch (\Exception $error) {
            $this->logger->error($this->getMessage("backup-failed"));
            $this->logger->error($error->getMessage());
            return false;
        }
        $this->logger->info($this->getMessage("backup-success"));
        return true;
    }

    /**
     * 設定の内容を取得する
     *
     * @return array
    */
    public function getSettings(): array
    {
        return $this->config->getAll();
    }

    /**
     * MoneySystemのバージョン情報を取得する
     *
     * @return string
    */
    public function getVersion(): string
    {
        return $this->mainSystem->getDescription()->getVersion();
    }

    /**
     * デフォルトの所持金を取得する
     *
     * @return int
    */
    public function getDefaultMoney(): int
    {
        return $this->getSettings()["default-money"];
    }

    /**
     * デフォルトの所持金を設定する
     *
     * @param integer $money
     * @return bool
    */
    public function setDefaultMoney(int $money): bool
    {
        $money = $this->check($money);
        $this->config->set("default-money", $money);
        $this->config->save();
        return true;
    }

    /**
     * アカウントを作成する
     *
     * @param string|Player $player
     * @param integer $money
     * @return boolean
     */
    public function createAccount($player, int $money = -1): bool
    {
        // GetMoneyTrait
        $this->getName($player);

        // 初期所持金に指定がない場合, デフォルト金額を設定
        if ($money < 0) {
            $money = $this->getDefaultMoney();
        }

        // アカウントが存在しない場合に作成
        if (!$this->exists($player)) {
            $this->accountDataArray[$player] = $money;
        } else {
            return false;
        }

        return true;
    }

    /**
     * アカウントを削除する
     *
     * @param string|Player $player
     * @return boolean
     */
    public function removeAccount($player): bool
    {
        // GetMoneyTrait
        $this->getName($player);

        // アカウントが存在しない場合
        if (!$this->exists($player)) {
            return false;
        }

        unset($this->accountDataArray[$player]);
        return true;
    }

    /**
     * アカウントが存在するかどうか
     *
     * @param string|Player $player
     * @return boolean
     */
    public function exists($player): bool
    {
        // GetNameTrait
        $this->getName($player);
        return isset($this->accountDataArray[$player]);
    }
}
