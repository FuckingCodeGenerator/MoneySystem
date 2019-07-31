<?php
namespace MoneySystemShop\event;

use pocketmine\event\{
	Listener,
	block\SignChangeEvent
};
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

use MoneySystemShop\MoneySystemShop as Main;

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
		if ($line[0] !== "shop")
			return;
		if (!$player->isOp()) {
			$player->sendPopup(TextFormat::RED . "あなたはアイテム販売看板を作成する権限がありません。");
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
        Main::$shop[$var] = [
			"X"        => $x,
			"Y"        => $y,
			"Z"        => $z,
			"Level"    => $level,
			"Item"     => $id,
			"ItemName" => $itemname,
			"Meta"     => $damage,
			"Price"    => $money,
        ];
		$player->sendPopup(TextFormat::AQUA . "アイテム販売看板を作成しました。");
		$ev->setLine(0, TextFormat::GREEN . TextFormat::BOLD . "[SHOP]");
		$ev->setLine(1, TextFormat::YELLOW . "Item: " . TextFormat::AQUA . $itemname);
		$ev->setLine(2, TextFormat::YELLOW . "Price: " . TextFormat::AQUA . $this->unit . $money); 
		$ev->setLine(3, "");
        return true;
    }
}
