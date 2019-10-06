<?php
declare(strict_types = 1);
namespace metowa1227\moneysystem\api\listener;

use pocketmine\utils\TextFormat;

interface Listener
{
    /**
     * 所持金操作の種類を表します
     * [増加: 1, 減少: 2, 設定: 3]
     * 
     * @var int
     */
    const TYPE_INCREASE = 1;
    const TYPE_REDUCE = 2;
    const TYPE_SET = 3;

    /**
     *  @param  string  | Player  $player
     *
     *  @return integer | null            money
     */
    public function get($player);

    /**
     *  @param  boolean  $key  If set to true, the name data of all accounts is returned as an array.
     *                         If set to false, full data of all accounts will be returned as an array.
     *
     *  @return array
     */
    public function getAll(bool $key = false): ?array;

    /**
     *  @return string  Returns the currency used by MoneySystem
     */
    public function getUnit(): string;

    /**
     *  @return boolean  Returns true if the save succeeded.
     */
    public function save(): bool;

    /**
     *  @param string | Player | array  $player  Target player information
     *  @param integer                  $money   Amount to be set
     *  @param string                   $calledBy      Practitioner
     *  @param string                   $reason  Clear reason set up
     *
     *  @return boolean  Returns true if the operation succeeded, false if it failed.
     */
    public function set($player, int $money, string $calledBy = "unknown", string $reason = "none"): bool;

    /**
     *  @param string | Player | array  $player  Target player information
     *  @param integer                  $money   Amount to be increase
     *  @param string                   $calledBy      Practitioner
     *  @param string                   $reason  Clear reason that increased
     *
     *  @return boolean  Returns true if the operation succeeded, false if it failed.
     */
    public function increase($player, int $money, string $calledBy = "unknown", string $reason = "none"): bool;

    /**
     *  @param string | Player | array  $player  Target player information
     *  @param integer                  $money   Amount to be reduce
     *  @param string                   $calledBy      Practitioner
     *  @param string                   $reason  Clear reason that reduced
     *
     *  @return boolean  Returns true if the operation succeeded, false if it failed.
     */
    public function reduce($player, int $money, string $calledBy = "unknown", string $reason = "none"): bool;

    /**
     *  @return boolean  Returns true if the backup succeeded.
     */
    public function backup(): bool;

    /**
     *  @return  It returns all settings as an array.
     */
    public function getSettings(): array;

    /**
     *  @return  Returns MoneySystem version.
     */
    public function getVersion(): float;

    /**
     *  @return  Acquires the default holding money and returns it.
     */
    public function getDefaultMoney(): int;

    /**
     *  @param  integer  $money  Amount to be set
     *
     *  @return boolean  Returns true if the setting is successful.
     */
    public function setDefaultMoney(int $money): bool;

    /**
     *  @param  string | Player  $player  Target information
     *  @param  integer          $money   Setting of money (If omitted, it will be created with the default amount.)
     */
    public function createAccount($player, int $money = -1): bool;

    /**
     *  @param  string | Player  $player  Information on the player who deletes the account
     *
     *  @return boolean  Returns true if the operation succeeded.
     */
    public function removeAccount($player): bool;

    /**
     *  @param  string | Player  $player  Target information
     *
     *  @return boolean  Returns true if the account exists, false if it does not exist.
     */
    public function exists($player): bool;

    /**
     * 言語データベース用の色データ
     *
     * @var string
    */
    const colorTag = [
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
    const color = [
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
}
