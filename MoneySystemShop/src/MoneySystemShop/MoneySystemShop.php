<?php
namespace MoneySystemShop;

use pocketmine\{
    Server,
    Player
};
use pocketmine\utils\{
    Config,
    TextFormat
};
use pocketmine\command\{
    Command,
    CommandSender
};
use pocketmine\network\mcpe\protocol\{
    ModalFormRequestPacket,
    ModalFormResponsePacket
};
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;

use metowa1227\moneysystem\api\core\API;
use MoneySystemShop\event\{
    Receive,
    TouchEvent,
    SignCreation,
    SignBreak
};
use MoneySystemShop\task\SaveTask;
use MoneySystemShop\command\IDCommand;

class MoneySystemShop extends PluginBase
{
    public static $shop, $tmp, $scheduler;
    public $formid, $unit;

    const PLUGIN_VERSION = 4.41;
    const PLUGIN_LAST_UPDATE = "2018/11/05";

	public function onEnable()
    {
        $this->api = API::getInstance();
        @mkdir($this->getDataFolder(), 0774, true);
        $this->object_shop = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
        self::$shop = $this->object_shop->getAll();
        $this->formid = (new Config($this->getDataFolder() . "FormIDs.yml", Config::YAML, [
            "OpenShop" => mt_rand(1, 555555),
            "BuyConfirm" => mt_rand(555556, 9999999)
        ]))->getAll();
        $this->unit = $this->api->getUnit();
        self::$scheduler = $this->getScheduler();
		$this->getServer()->getPluginManager()->registerEvents(new Receive($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new TouchEvent($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignCreation($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new SignBreak(), $this);
        $this->getScheduler()->scheduleRepeatingTask(new SaveTask($this->object_shop, $this), 20 * 60 * 5);
        $this->getServer()->getCommandMap()->register("id", new IDCommand());
        $this->getLogger()->notice(TextFormat::GREEN . "MoneySystemShop has started.");
	}

    public function onDisable()
    {
        $this->object_shop->setAll(self::$shop);
        $this->object_shop->save();
    }

    public static function getTaskScheduler()
    {
        return self::$scheduler;
    }
}
