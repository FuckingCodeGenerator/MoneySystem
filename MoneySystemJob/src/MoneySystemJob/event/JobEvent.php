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
					if (!API::getInstance()->increase($player, $price))
						$player->sendPopup("You could not receive work reward.");
                }
            }
        }
	}
}
