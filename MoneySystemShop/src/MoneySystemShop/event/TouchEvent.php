<?php
namespace MoneySystemShop\event;

use pocketmine\event\{
	Listener,
	player\PlayerInteractEvent
};
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\Task;
use pocketmine\Player;

use MoneySystemShop\{
	MoneySystemShop as Main,
	form\SendForm
};

class TouchEvent extends SendForm implements Listener
{
	public function __construct(Main $main)
	{
		$this->main = $main;
		$this->formid = $main->formid;
	}

	public function onTouch(PlayerInteractEvent $ev)
	{
        if ($ev->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            return 0;
        }
        
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
		$var = $block->x . ":" . $block->y . ":" . $block->z . ":" . $block->getLevel()->getFolderName();
		if (!isset(Main::$shop[$var]))
			return true;
        for ($i = 0; $i <= 64; $i++)
            $item64[] = "" . $i . "";
        $shop = Main::$shop[$var];
        $slider[] = [
            'type' => "step_slider",
            'text' => "\n\n購入する個数を選択してください。\nアイテム詳細:\n- アイテム名: " . $shop["ItemName"] . "\n- アイテムID: " . $shop["Item"] . " : " . $shop["Meta"] . "\n- 一個あたりの値段: " . $shop["Price"] . "\n個数",
            'steps' => $item64,
            'defaultIndex' => "1"
        ];
        $data = [
            "type"    => "custom_form",
            "title"   => TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "ShoppingCart",
            "content" => $slider,
        ];
        $this->sendForm($player, $data);
        Main::$tmp[$player->getName()] = $var;
	}
}
