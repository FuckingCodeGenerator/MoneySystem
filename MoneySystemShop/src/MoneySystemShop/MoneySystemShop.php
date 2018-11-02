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

/*
    PluginIntrodtion
    - CONTENTS
        - Provide a system for players to trade items in cooperation with MoneySystemAPI.
    - AUTHOR
        - metowa1227 (MoneySystemAPI)
        - metowa1227 (This plugin (MoneySystemShop))
    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Pro 64bit
        - Intel(R) Core 2 Duo(TM) E8400 @ 3.00GHz
        - 8192MB DDR2 SDRAM PC2-5300(667MHz) , PC2-6400(800MHz)
        - 1.7dev-1001「[REDACTED]」Minecraft PE v1.4.0用実装APIバージョン3.0.0-ALPHA12(プロトコルバージョン261)
        - PHP 7.2.1 64bit supported version
        - MoneySystemAPI (SYSTEM) version 12.1 package version 12.00 API version 11.1 GREEN PAPAYA GT3 Edition (Releaced date: 2018/06/09)
*/

namespace MoneySystemShop;

use pocketmine\{ Server, Player };
use pocketmine\utils\{ Config, TextFormat };
use pocketmine\event\block\{ SignChangeEvent, BlockBreakEvent };
use pocketmine\command\{ Command, CommandSender };
use pocketmine\network\mcpe\protocol\{ ModalFormRequestPacket, ModalFormResponsePacket };
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\event\server\DataPacketReceiveEvent;

use metowa1227\moneysystem\api\core\API;

class MoneySystemShop extends PluginBase implements Listener
{

    /* @var $id formId */
    public $id = null;

    /* @var $data formJsonData */
    private $data = null;

    /* @var $item128 Max count */
    private $item128 = null;

    /* @var $confirm Buy item cofirm */
    public $confirm = false;

    /* @var $amount Item amount */
    public $amount = null;

    const PLUGIN_VERSION     = 4.0;
    const PLUGIN_AUTHOR      = "metowa1227";
    const BASED_ECONOMY_API  = "MoneySystemAPI";
    const BASED_ECONOMY      = "MoneySystem";
    const PLUGIN_LAST_UPDATE = "2018/09/01";

	public function onEnable()
    {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->moneyapi = API::getInstance();
        if ($this->moneyapi === null) {
            $this->getLogger()->error("MoneySystemAPIが導入されていません。");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return false;
        }
        if (!file_exists($this->getDataFolder())) {
            @mkdir($this->getDataFolder(), 0774, true);
        }
        $this->shop = new Config($this->getDataFolder() . "shop.yml",Config::YAML);
        $this->getLogger()->notice(TextFormat::GREEN . "MoneySystemShopを起動しました。");
        $this->getLogger()->notice(
            "PluginInfo version: " . self::PLUGIN_VERSION . " author: " . self::PLUGIN_AUTHOR . " last update: " . self::PLUGIN_LAST_UPDATE
        );
	}

	public function onSign(SignChangeEvent $event)
    {
		$block  = $event->getBlock();
		$player = $event->getPlayer();
		if ($event->getLine(0) === "shop") {
			if (!$player->isOp()) {
				$player->sendMessage(TextFormat::RED . "あなたはアイテム販売看板を作成する権限がありません。");
				return false;
            }
            if (!is_numeric($event->getLine(2))) {
				$player->sendPopup(
                    TextFormat::RED . "記入方法が違います。\n" . TextFormat::RESET . TextFormat::RED . "一行目に「shop」、2行目にアイテムID:META、3行目に1個あたりの値段を入力してください。\n" . TextFormat::RESET . TextFormat::RED . "アイテムIDは調べるアイテムを手に持った状態で「/id」と実行することで検索できます。"
                );
				return false;
            }
			$item = Item::fromString($event->getLine(1));
			$var  = (Int) $event->getBlock()->getX() . ":" .
                    (Int) $event->getBlock()->getY() . ":" .
                    (Int) $event->getBlock()->getZ() . ":" .
                    $block->getLevel()->getFolderName();
            $this->shop->set($var,
                [
    				"X"        => $block->getX(),
    				"Y"        => $block->getY(),
    				"Z"        => $block->getZ(),
    				"Level"    => $block->getLevel()->getFolderName(),
    				"Item"     => $item->getID(),
    				"ItemName" => $item->getName(),
    				"Meta"     => $item->getDamage(),
    				"Price"    => $event->getLine(2),
                ]
            );
            $this->shop->save();
            $id       = $item->getID();
            $damage   = $item->getDamage();
            $itemname = $item->getName();
            $money    = $event->getLine(2);
			$player->sendPopup(TextFormat::AQUA   . "アイテム販売看板を作成しました。");
			$event->setLine(0, TextFormat::GREEN  . TextFormat::BOLD . "[SHOP]");
			$event->setLine(1, TextFormat::YELLOW . "Item: "  . TextFormat::AQUA . TextFormat::ITALIC . $itemname);
			$event->setLine(2, TextFormat::YELLOW . "Price: " . TextFormat::AQUA . TextFormat::ITALIC . $this->moneyapi->getUnit() . $money); 
			$event->setLine(3, "");
            return true;
        }
    }

    public function onTouch(PlayerInteractEvent $event)
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $loc = $block->getX() . ":" .
               $block->getY() . ":" .
               $block->getZ() . ":" .
               $block->getLevel()->getFolderName();
		$var = $block->getX() . ":" .
               $block->getY() . ":" .
               $block->getZ() . ":" .
               $block->getLevel()->getFolderName();
		if ($this->shop->exists($var)) {
            $shop = $this->shop->get($var);
            $this->id[$player->getName()]["shopData"] = $shop;
            for ($i = 0; $i <= 64; $i++) {
                $item128[] = "" . $i . "";
            }
            $slider[] = [
                'type' => "step_slider",
                'text' => "購入する個数を選択してください。\nアイテム詳細:\n- アイテム名: " . $shop["ItemName"] . "\n- アイテムID: " . $shop["Item"] . " : " . $shop["Meta"] . "\n- 一個あたりの値段: " . $shop["Price"] . "\n個数",
                'steps' => $item128,
                'defaultIndex' => "1"
            ];
            $data = [
                "type"    => "custom_form",
                "title"   => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "ShoppingCart",
                "content" => $slider,
            ];
            $this->buyItemForm($player, $data);
        }
    }

    public function onBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $block  = $event->getBlock();
        $x      = $block->getX();
        $y      = $block->getY();
        $z      = $block->getZ();
        $world  = $block->getLevel()->getName();
        $name   = $player->getName(); 
        $var    = (Int) $event->getBlock()->getX() . ":" .
                  (Int) $event->getBlock()->getY() . ":" .
                  (Int) $event->getBlock()->getZ() . ":" . $world;
        if ($this->shop->exists($var)) {
            if ($player->isOp()) {
                $this->shop->remove($var);
                $this->shop->save();
                $player->sendMessage(TextFormat::GREEN . "アイテム販売看板を取り壊しました。");
            } else {
                $player->sendMessage(TextFormat::RED . "あなたはアイテム販売看板を取り壊す権限がありません。");
                $event->setCancelled();
            }
        }
    }

    public function onCommand (CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        switch ($command->getName()) {
            case "id":
                $item = $sender->getInventory()->getItemInHand();
                $id   = $item->getID();
                $meta = $item->getDamage();
                $sender->sendMessage("[ID] 手に持っているアイテムは、" . $id . ":" . $meta . "です。");
                return true;
                break;
        }
    }

    private function buyItemForm(Player $player, array $data)
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = 38453935;
        $pk->formData = json_encode($data);
        $player->dataPacket($pk);
        $this->id[$player->getName()]["formId"] = $pk->formId;
    }

    private function buyConfirm(Player $player, array $data)
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = 35647565;
        $pk->formData = json_encode($data);
        $player->dataPacket($pk);
    }

    public function onDataReceived(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        if ($packet instanceof ModalFormResponsePacket) {
            $player   = $event->getPlayer();
            $formId   = $packet->formId;
            $formData = json_decode($packet->formData, true);
            switch ($formId) {
                case 38453935:
                    $shop = $this->id[$player->getName()]["shopData"];
                    if (isset($this->id[$player->getName()]) && $this->id[$player->getName()]["formId"] === $formId && isset($formData)) {
                        if (!$player->getInventory()->canAddItem(Item::get($shop["Item"], $shop["Meta"], $formData[0]))) {
                            for ($i = 0; $i <= 64; $i++) {
                                $item128[] = "" . $i . "";
                            }
                            $slider[] = [
                                'type' => "step_slider",
                                'text' => TextFormat::YELLOW . "インベントリの容量が不足しています。\n" . TextFormat::RESET . "購入する個数を選択してください。\nアイテム詳細:\n- アイテム名: " . $shop["ItemName"] . "\n- アイテムID: " . $shop["Item"] . " : " . $shop["Meta"] . "\n- 一個あたりの値段: " . $shop["Price"] . "\n個数",
                                'steps' => $item128,
                                'defaultIndex' => "1"
                            ];
                            $data = [
                                "type"    => "custom_form",
                                "title"   => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "ShoppingCart",
                                "content" => $slider,
                            ];
                            $this->buyItemForm($player, $data);
                            return false;
                        }
                        $nowmoney = $this->moneyapi->get($player);
                        $price    = $formData[0] * $shop["Price"];
                        if ($nowmoney < $price) {
                            $lack = $price - $nowmoney;
                            for ($i = 0; $i <= 64; $i++) {
                                $item128[] = "" . $i . "";
                            }
                            $slider[] = [
                                'type' => "step_slider",
                                'text' => TextFormat::YELLOW . "所持金が不足しています。(" . $this->moneyapi->getUnit() . $lack . ")\n" . TextFormat::RESET . "購入する個数を選択してください。\nアイテム詳細:\n- アイテム名: " . $shop["ItemName"] . "\n- アイテムID: " . $shop["Item"] . " : " . $shop["Meta"] . "\n- 一個あたりの値段: " . $shop["Price"] . "\n個数",
                                'steps' => $item128,
                                'defaultIndex' => "1"
                            ];
                            $data = [
                                "type"    => "custom_form",
                                "title"   => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "ShoppingCart",
                                "content" => $slider,
                            ];
                            $this->buyItemForm($player, $data);
                            return false;
                        }
                        $data = [
                            "type"    => "modal",
                            "title"   => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "ShoppingCart",
                            "content" => $shop["ItemName"] . "(" . $formData[0] . "個)を" . $this->moneyapi->getUnit() . $price . "で購入します。",
                            "button1" => "購入",
                            "button2" => "キャンセル"
                        ];
                        $this->buyConfirm($player, $data);
                        $this->amount[$player->getName()]  = $formData[0];
                        $this->confirm[$player->getName()] = true;
                        return true;
                    } else {
                        $player->sendMessage(TextFormat::GREEN . "ユーザーによってアイテム購入が取り消されました。");
                        return false;
                    }
                    break;

                case 35647565:
                    if (isset($this->id[$player->getName()]["shopData"]) && isset($this->confirm[$player->getName()])) {
                        if ($this->confirm[$player->getName()] === true) {
                            if (!$formData) {
                                return false;
                            } else {
                                $shop  = $this->id[$player->getName()]["shopData"];
                                $price = $shop["Price"] * $this->amount[$player->getName()];
                                $player->getInventory()->addItem(Item::get($shop["Item"], $shop["Meta"], $this->amount[$player->getName()]));
                                $this->moneyapi->reduce($player->getName(), $price);
                                $player->sendMessage(
                                    TextFormat::GREEN . $shop["ItemName"] . "( " . $this->amount[$player->getName()] . "個 )" .
                                    "を" . $this->moneyapi->getUnit() . $price . "で購入しました。"
                                );
                                $this->id[$player->getName()]      = null;
                                $this->amount[$player->getName()]  = null;
                                $this->confirm[$player->getName()] = false;
                                return true;
                            }
                        }
                    }
                    break;
            }
        }
    }
}
