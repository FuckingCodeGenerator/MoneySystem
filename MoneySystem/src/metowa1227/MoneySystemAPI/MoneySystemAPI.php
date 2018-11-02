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

namespace metowa1227\MoneySystemAPI;

use metowa1227\moneysystem\api\core\API;
use metowa1227\moneysystem\core\System;

class MoneySystemAPI
{
    public static $instance = null;

    public function __construct(System $main)
    {
        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    public function getDefaultLang(string $name)
    {
        return "english";
    }

    public function getMessage(string $name, $message)
    {
        return "This MoneySystem is not supported.";
    }

    public function existsLang(string $lang) : bool
    {
        return false;
    }

    public function setLang(string $name, $lang) : bool
    {
        return false;
    }

    public function check($player)
    {
        return API::getInstance()->get($player);
    }

    public function addMoney($player, int $money, $reason = "none", $by = "unknown") : bool
    {
        return API::getInstance()->increase($player, $money, $reason, $by);
    }

    public function takeMoney($player, int $money, $reason = "none", $by = "unknown", $debt = false) : bool
    {
        return API::getInstance()->reduce($player, $money, $reason, $by);
    }

    public function setMoney($player, int $money, $reason = "none", $by = "unknown", $debt = false) : bool
    {
        return API::getInstance()->set($player, $money, $reason, $by);
    }

    public function createAccount($player, $custom = false, $money = 3000) : bool
    {
        return API::getInstance()->createAccount($player, $money);
    }

    public function removeAccount($player) : bool
    {
        return API::getInstance()->removeAccount($player);
    }

    public function setPlayerDefaultMoney($player) : bool
    {
        return API::getInstance()->set($player, $this->getDefaultMoney());
    }

    public function getDefaultMoney() : int
    {
        return API::getInstance()->getDefaultMoney();
    }

    public function setAllDefaultMoney() : bool
    {
        foreach (API::getInstance()->getAll(true) as $data) {
            API::getInstance()->set($data, $this->getDefaultMoney());
        }
        return true;
    }

    public function setDefaultMoney(int $money) : bool
    {
        return API::getInstance()->setDefaultMoney($money);
    }

    public function setAllCustomMoney(int $money) : bool
    {
        foreach (API::getInstance()->getAll(true) as $data) {
            API::getInstance()->set($data, $money);
        }
        return true;
    }

    public function dataSave() : bool
    {
        return API::getInstance()->save();
    }

    public function databaseType() : string
    {
        return "SQLITEFILE";
    }

    public function getMonitorUnit() : string
    {
        return API::getInstance()->getUnit();
    }

    public function isEnable() : bool
    {
        return true;
    }

    public function backupFiles() : bool
    {
        return API::getInstance()->backup();
    }

    public function files_exists()
    {
        return true;
    }

    public function backedup_files_exists()
    {
        return true;
    }

    public function existsAccount($player) : bool
    {
        return API::getInstance()->exists($player);
    }

    public function getAllMoneyData() : array
    {
        return API::getInstance()->getAll();
    }

    public function getAllMoneyNameData() : array
    {
        return API::getInstance()->getAll(true);
    }

    public function debtMode() : string
    {
        return "NONE";
    }

    public function isDebt($player)
    {
        return false;
    }

    public function debtPlayersList() : array
    {
        return [];
    }

    public function writeOff($player) : bool
    {
        return false;
    }
}
