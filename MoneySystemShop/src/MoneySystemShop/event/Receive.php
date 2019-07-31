<?php
namespace MoneySystemShop\event;

use pocketmine\utils\{
	Config,
	TextFormat
};
use pocketmine\event\{
	Listener,
	server\DataPacketReceiveEvent
};
use pocketmine\network\mcpe\protocol\{
	ModalFormResponsePacket,
	ModalFormRequestPacket
};
use pocketmine\item\Item;

use metowa1227\moneysystem\api\core\API;
use MoneySystemShop\{
	MoneySystemShop as Main,
	form\SendForm
};

class Receive extends SendForm implements Listener
{
	public function __construct(Main $main)
	{
		$this->formid = $main->formid;
	}

    public function onDataReceived(DataPacketReceiveEvent $ev)
    {
        $packet = $ev->getPacket();
        if (!$packet instanceof ModalFormResponsePacket)
        	return;
        $player = $ev->getPlayer();
        $name = $player->getName();
        $formId = $packet->formId;
        $formData = json_decode($packet->formData, true);
        $api = API::getInstance();
        switch ($formId) {
            case $this->formid["OpenShop"]:
                $shop = Main::$shop[Main::$tmp[$name]];
                if (!isset($formData)) {
                	unset(Main::$tmp[$name]);
                	return true;
                }
                $count = $formData[0];
                if ($count === 0)
                	return true;

                if (!$player->getInventory()->canAddItem(Item::get($shop["Item"], $shop["Meta"], $count))) {
                    for ($i = 0; $i <= 64; $i++)
                        $item64[] = "" . $i . "";
                    $slider[] = [
                        'type' => "step_slider",
                        'text' => TextFormat::YELLOW . "\n\nインベントリの容量が不足しています。\n" . TextFormat::RESET . "購入する個数を選択してください。\nアイテム詳細:\n- アイテム名: " . $shop["ItemName"] . "\n- アイテムID: " . $shop["Item"] . " : " . $shop["Meta"] . "\n- 一個あたりの値段: " . $shop["Price"] . "\n個数",
                        'steps' => $item64,
                        'defaultIndex' => "1"
                    ];
                    $data = [
                        "type"    => "custom_form",
                        "title"   => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "ShoppingCart",
                        "content" => $slider,
                    ];
                    $this->sendForm($player, $data);
                    return true;
                }

                $money = $api->get($player);
                $price = $count * $shop["Price"];
                if ($money < $price) {
                    $lack = $price - $money;
                    for ($i = 0; $i <= 64; $i++)
                        $item64[] = "" . $i . "";
                    $slider[] = [
                        'type' => "step_slider",
                        'text' => TextFormat::YELLOW . "\n\n所持金が不足しています。(" . $api->getUnit() . $lack . ")\n" . TextFormat::RESET . "購入する個数を選択してください。\nアイテム詳細:\n- アイテム名: " . $shop["ItemName"] . "\n- アイテムID: " . $shop["Item"] . " : " . $shop["Meta"] . "\n- 一個あたりの値段: " . $shop["Price"] . "\n個数",
                        'steps' => $item64,
                        'defaultIndex' => "1"
                    ];
                    $data = [
                        "type"    => "custom_form",
                        "title"   => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "ShoppingCart",
                        "content" => $slider,
                    ];
                    $this->sendForm($player, $data);
                    return true;
                }

                $data = [
                    "type"    => "modal",
                    "title"   => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "ShoppingCart",
                    "content" => $shop["ItemName"] . "(" . $count . "個)を" . $api->getUnit() . $price . "で購入します。",
                    "button1" => "購入",
                    "button2" => "キャンセル"
                ];
                $this->sendForm($player, $data, true);
                $this->count[$name] = $count;
                $this->confirm[$name] = true;
                return true;
            	break;

            case $this->formid["BuyConfirm"]:
                if (!isset($this->confirm[$name]))
                	return true;
                if ($formData) {
	                $shop = Main::$shop[Main::$tmp[$name]];
	                $count = $this->count[$name];
	                $price = $shop["Price"] * $count;
		            $api->reduce($name, $price, "アイテムの購入");
	                $player->getInventory()->addItem((new Item($shop["Item"], $shop["Meta"]))->setCount($count));
	                $player->sendMessage(TextFormat::GREEN . $shop["ItemName"] . "( " . $count . "個 )" . "を" . $api->getUnit() . $price . "で購入しました。");
                }
                unset($this->confirm[$name], $this->count[$name]);
                return true;
	            break;
        }
    }
}
