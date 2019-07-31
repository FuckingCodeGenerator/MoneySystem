<?php
namespace MoneySystemShop\event;

use pocketmine\event\{
	Listener,
	block\BlockBreakEvent
};
use pocketmine\utils\TextFormat;

use MoneySystemShop\MoneySystemShop as Main;

class SignBreak implements Listener
{
    public function onBreak(BlockBreakEvent $ev)
    {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
		$x = intval($block->x);
		$y = intval($block->y);
		$z = intval($block->z);
		$level = $block->getLevel()->getFolderName();
		$var = $x . ":" . $y . ":" . $z . ":" . $level;
        if (!isset(Main::$shop[$var]))
        	return;
        if (!$player->isOp()) {
            $player->sendMessage(TextFormat::RED . "あなたはアイテム販売看板を取り壊す権限がありません。");
            $ev->setCancelled();
            return;
        }
        unset(Main::$shop[$var]);
        $player->sendMessage(TextFormat::GREEN . "アイテム販売看板を取り壊しました。");
        return true;
    }
}
