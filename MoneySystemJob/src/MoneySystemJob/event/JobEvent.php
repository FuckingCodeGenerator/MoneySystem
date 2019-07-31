<?php
namespace MoneySystemJob\event;

use pocketmine\Player;

use MoneySystemJob\api\JobAPI;

use metowa1227\moneysystem\api\core\API;

class JobEvent
{
	public static function processEvent(Player $player, $block, $type)
	{
		$api = JobAPI::getInstance();
		if (empty($job = $api->getJob($player)))
			return;
		$list = $api->getJobList();
        foreach ($list as $key => $list1) {
        	if (strtolower($key) !== strtolower($type))
        		continue;
            foreach ($list1 as $job => $list2) {
                if ($job !== $api->getJob($player))
                    continue;
                foreach ($list2 as $item => $price) {
                	$id = $block->getId() . ":" . $block->getDamage();
                	if ($id !== $item)
                		continue;
					if (!API::getInstance()->increase($player, $price, "MoneySystemJob"))
						$player->sendPopup("You could not receive work reward.");
                }
            }
        }
	}
}
