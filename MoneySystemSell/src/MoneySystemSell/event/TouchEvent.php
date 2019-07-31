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

namespace MoneySystemSell\event;

use pocketmine\event\{
	Listener,
	player\PlayerInteractEvent
};
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\Task;
use pocketmine\Player;

use MoneySystemSell\{
	MoneySystemSell as Main,
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
		if (!isset(Main::$sell[$var]))
			return true;
        for ($i = 0; $i <= 64; $i++)
            $item64[] = "" . $i . "";
        $sell = Main::$sell[$var];
        $slider[] = [
            'type' => "step_slider",
            'text' => "\n\n売却する個数を選択してください。\nアイテム詳細:\n- アイテム名: " . $sell["ItemName"] . "\n- アイテムID: " . $sell["Item"] . " : " . $sell["Meta"] . "\n- 一個あたりの売価: " . $sell["Price"] . "\n個数",
            'steps' => $item64,
            'defaultIndex' => "1"
        ];
        $data = [
            "type"    => "custom_form",
            "title"   => TextFormat::BLUE . TextFormat::BOLD . "SellingItems",
            "content" => $slider,
        ];
        $this->sendForm($player, $data);
        Main::$tmp[$player->getName()] = $var;
	}
}
