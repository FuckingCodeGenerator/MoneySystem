<?php
namespace metowa1227\moneysystem\api\processor;

use pocketmine\Player;

trait GetName
{
    protected function getName(&$player) : void
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
    }
}
