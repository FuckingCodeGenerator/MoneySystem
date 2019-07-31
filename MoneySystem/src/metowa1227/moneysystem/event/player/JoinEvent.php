<?php
namespace metowa1227\moneysystem\event\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use metowa1227\moneysystem\api\core\API;

class JoinEvent implements Listener
{
    public function onJoin(PlayerJoinEvent $event)
    {
        $api = API::getInstance();
        $player = $event->getPlayer();
        $name = $player->getName();
        $api->createAccount($player);
    }
}
