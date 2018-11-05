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
