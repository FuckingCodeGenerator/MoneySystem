<?php

/*
*  __  __       _                             __    ___    ___   _______
* |  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
* | |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
* | |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
* |_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
* All this program is made by hand of metowa1227.
* I certify here that all authorities are in metowa1227.
* Expiration date of certification: unlimited
* Secondary distribution etc are prohibited.
* The update is also done by the developer.
* This plugin is a developer API plugin to make it easier to write code.
* When using this plug-in, be sure to specify it somewhere.
* Warning if violation is confirmed.
*
* Developer: metowa1227
*/

/*
    Plugin description

    - CONTENTS
        - Lightweight, fast and multifunctional economic system.

    - AUTHOR
        - metowa1227 (MoneySystemAPI)

    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Pro 64bit
        - Intel(R) Core 2 Duo(TM) E8400 @ 3.00GHz
        - 8192MB DDR2 SDRAM PC2-5300(667MHz) , PC2-6400(800MHz)
        - Altay 3.0.6+dev for Minecraft: PE v1.5.0 (protocol version 274)
        - PHP 7.2.1 64bit supported version
*/

namespace metowa1227\moneysystem\event\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use metowa1227\moneysystem\api\core\API;

class JoinEvent implements Listener
{
    public function __construct() {}

    public function onJoin(PlayerJoinEvent $event)
    {
        $api    = API::getInstance();
        $player = $event->getPlayer();
        $name   = $player->getName();
        if (!$api->exists($player))
            $api->createAccount($player);
        $cache = $api->hasCache($player);
        if ($cache["cache"] !== 0) {
            $api->increase($player, $cache["cache"]);
            $player->sendMessage($api->getMessage("join.payment", [$cache["by"], $api->getUnit(), $cache["cache"]]));
            $api->removeCache($player);
        }
    }
}
