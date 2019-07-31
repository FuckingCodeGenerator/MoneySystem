<?php
namespace MoneySystemSell\event;

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
use MoneySystemSell\{
	MoneySystemSell as Main,
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
            case $this->formid["OpenSell"]:
                $sell = Main::$sell[Main::$tmp[$name]];
                if (!isset($formData)) {
                	unset(Main::$tmp[$name]);
                	return true;
                }
                $count = $formData[0];
                if ($count === 0)
                	return true;

                if (!$player->getInventory()->contains(Item::get($sell["Item"], $sell["Meta"])->setCount($count))) {
                    for ($i = 0; $i <= 64; $i++)
                        $item64[] = "" . $i . "";
                    $slider[] = [
                        'type' => "step_slider",
                        'text' => TextFormat::YELLOW . "\n\nアイテムの個数が不足しています。\n" . TextFormat::RESET . "売却する個数を選択してください。\nアイテム詳細:\n- アイテム名: " . $sell["ItemName"] . "\n- アイテムID: " . $sell["Item"] . " : " . $sell["Meta"] . "\n- 一個あたりの売価: " . $sell["Price"] . "\n個数",
                        'steps' => $item64,
                        'defaultIndex' => "1"
                    ];
                    $data = [
                        "type"    => "custom_form",
                        "title"   => TextFormat::BLUE . TextFormat::BOLD . "SellingItems",
                        "content" => $slider,
                    ];
                    $this->sendForm($player, $data);
                    return true;
                }

                $price = $count * $sell["Price"];
                $data = [
                    "type"    => "modal",
                    "title"   => TextFormat::BLUE . TextFormat::BOLD . "SellingItems",
                    "content" => $sell["ItemName"] . "(" . $count . "個)を" . $api->getUnit() . $price . "で売却します。",
                    "button1" => "売却",
                    "button2" => "キャンセル"
                ];
                $this->sendForm($player, $data, true);
                $this->count[$name] = $count;
                $this->confirm[$name] = true;
                return true;
            	break;

            case $this->formid["SellConfirm"]:
                if (!isset($this->confirm[$name]))
                	return true;
                if ($formData) {
	                $sell = Main::$sell[Main::$tmp[$name]];
	                $count = $this->count[$name];
	                $price = $sell["Price"] * $count;
		            $api->increase($name, $price, "アイテムの売却");
	                $player->getInventory()->removeItem((new Item($sell["Item"], $sell["Meta"]))->setCount($count));
	                $player->sendMessage(TextFormat::GREEN . $sell["ItemName"] . "( " . $count . "個 )" . "を" . $api->getUnit() . $price . "で売却しました。");
                }
                unset($this->confirm[$name], $this->count[$name]);
                return true;
	            break;
        }
    }
}
