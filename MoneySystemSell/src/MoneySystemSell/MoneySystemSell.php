<?php

/*
* __  __       _                             __    ___    ___   _______
*|  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
*| |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
*| |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
*|_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
*All this program is made by hand of metowa 1227.
*I certify here that all authorities are in metowa 1227.
*Expiration date of certification: unlimited
*Secondary distribution etc are prohibited.
*The update is also done by the developer.
*This plugin is a developer API plugin to make it easier to write code.
*When using this plug-in, be sure to specify it somewhere.
*Warning if violation is confirmed.
*
*Developer: metowa 1227
*Development Team: metowa 1227 Plugin Development Team (Members: metowa 1227 only)
*/

namespace MoneySystemSell;

use pocketmine\{
    Server,
    Player
};
use pocketmine\utils\{
    Config,
    TextFormat
};
use pocketmine\network\mcpe\protocol\{
    ModalFormRequestPacket,
    ModalFormResponsePacket
};
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;

use metowa1227\moneysystem\api\core\API;
use MoneySystemSell\event\{
    Receive,
    TouchEvent,
    SignCreation,
    SignBreak
};
use MoneySystemSell\task\SaveTask;

class MoneySystemSell extends PluginBase
{
    public static $sell, $tmp, $scheduler;
    public $formid, $unit;

    const PLUGIN_VERSION = 4.41;
    const PLUGIN_LAST_UPDATE = "2018/11/05";

	public function onEnable()
    {
        $this->api = API::getInstance();
        @mkdir($this->getDataFolder(), 0774, true);
        $this->object_sell = new Config($this->getDataFolder() . "sell.yml", Config::YAML);
        self::$sell = $this->object_sell->getAll();
        $this->formid = (new Config($this->getDataFolder() . "FormIDs.yml", Config::YAML, [
            "OpenSell" => mt_rand(1, 555555),
            "SellConfirm" => mt_rand(555556, 9999999)
        ]))->getAll();
        $this->unit = $this->api->getUnit();
        self::$scheduler = $this->getScheduler();
		$this->getServer()->getPluginManager()->registerEvents(new Receive($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new TouchEvent($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignCreation($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignBreak(), $this);
        $this->getScheduler()->scheduleRepeatingTask(new SaveTask($this->object_sell, $this), 20 * 60 * 5);
        $this->getLogger()->notice(TextFormat::GREEN . "MoneySystemSell has started.");
	}

    public function onDisable()
    {
        $this->object_sell->setAll(self::$sell);
        $this->object_sell->save();
    }

    public static function getTaskScheduler()
    {
        return self::$scheduler;
    }
}
