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
	block\SignChangeEvent
};
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

use MoneySystemSell\MoneySystemSell as Main;

class SignCreation implements Listener
{
	public function __construct(Main $main)
	{
		$this->unit = $main->unit;
	}

	public function onSignCreate(SignChangeEvent $ev)
    {
		$player = $ev->getPlayer();
		$block = $ev->getBlock();
		$line = $ev->getLines();
		if ($line[0] !== "sell")
			return;
		if (!$player->isOp()) {
			$player->sendPopup(TextFormat::RED . "あなたはアイテム売却看板を作成する権限がありません。");
			return false;
        }
        if (!ctype_digit($line[2]))
			return false;
		$item = Item::fromString($line[1]);
		$x = intval($block->x);
		$y = intval($block->y);
		$z = intval($block->z);
		$level = $block->getLevel()->getFolderName();
		$var = $x . ":" . $y . ":" . $z . ":" . $level;
        $id = $item->getId();
        $damage = $item->getDamage();
        $itemname = $item->getName();
        $money = $line[2];
        Main::$sell[$var] = [
			"X"        => $x,
			"Y"        => $y,
			"Z"        => $z,
			"Level"    => $level,
			"Item"     => $id,
			"ItemName" => $itemname,
			"Meta"     => $damage,
			"Price"    => $money,
        ];
		$player->sendPopup(TextFormat::AQUA . "アイテム売却看板を作成しました。");
		$ev->setLine(0, TextFormat::GREEN . TextFormat::BOLD . "[SELL]");
		$ev->setLine(1, TextFormat::YELLOW . "Item: " . TextFormat::AQUA . $itemname);
		$ev->setLine(2, TextFormat::YELLOW . "Price: " . TextFormat::AQUA . $this->unit . $money); 
		$ev->setLine(3, "");
        return true;
    }
}
