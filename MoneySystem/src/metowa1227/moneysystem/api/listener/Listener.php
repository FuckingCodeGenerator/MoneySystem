<?php
declare(strict_types = 1);

namespace metowa1227\moneysystem\api\listener;

interface Listener
{
    /**
     *  @param  string  | Player  $player
     *  @param  boolean           $array          If set to true, account data is returned as an array.
     *
     *  @return integer | array | null            Account data or money
    **/
    public function get($player, bool $array = false);

    /**
     *  @param  boolean  $key  If set to true, the name data of all accounts is returned as an array.
     *                         If set to false, full data of all accounts will be returned as an array.
     *
     *  @return array
    **/
    public function getAll(bool $key = false) : ?array;

    /**
     *  @return string  Returns the currency used by MoneySystem
    **/
    public function getUnit() : string;

    /**
     *  @return boolean  Returns true if the save succeeded.
    **/
    public function save() : bool;

    /**
     *  @param string | Player | array  $player  Target player information
     *  @param integer                  $money   Amount to be set
     *  @param string                   $by      Practitioner
     *  @param string                   $reason  Clear reason set up
     *
     *  @return boolean  Returns true if the operation succeeded, false if it failed.
    **/
    public function set($player, int $money, string $by = "unknown", string $reason = "none") : bool;

    /**
     *  @param string | Player | array  $player  Target player information
     *  @param integer                  $money   Amount to be increase
     *  @param string                   $by      Practitioner
     *  @param string                   $reason  Clear reason that increased
     *
     *  @return boolean  Returns true if the operation succeeded, false if it failed.
    **/
    public function increase($player, int $money, string $by = "unknown", string $reason = "none") : bool;

    /**
     *  @param string | Player | array  $player  Target player information
     *  @param integer                  $money   Amount to be reduce
     *  @param string                   $by      Practitioner
     *  @param string                   $reason  Clear reason that reduced
     *
     *  @return boolean  Returns true if the operation succeeded, false if it failed.
    **/
    public function reduce($player, int $money, string $by = "unknown", string $reason = "none") : bool;

    /**
     *  @return boolean  Returns true if the backup succeeded.
    **/
    public function backup() : bool;

    /**
     *  @return  It returns all settings as an array.
    **/
    public function getSettings() : array;

    /**
     *  @return  Returns MoneySystem version.
    **/
    public function getVersion() : float;

    /**
     *  @return  Acquires the default holding money and returns it.
    **/
    public function getDefaultMoney() : int;

    /**
     *  @param  integer  $money  Amount to be set
     *
     *  @return boolean  Returns true if the setting is successful.
    **/
    public function setDefaultMoney(int $money) : bool;

    /**
     *  @param  string | Player  $player  Target information
     *  @param  integer          $money   Setting of money (If omitted, it will be created with the default amount.)
    **/
    public function createAccount($player, int $money = -1) : bool;

    /**
     *  @param  string | Player  $player  Information on the player who deletes the account
     *
     *  @return boolean  Returns true if the operation succeeded.
    **/
    public function removeAccount($player) : bool;

    /**
     *  @param  string | Player  $player  Target information
     *
     *  @return boolean  Returns true if the account exists, false if it does not exist.
    **/
    public function exists($player) : bool;
}
