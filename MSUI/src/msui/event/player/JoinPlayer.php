<?php
namespace msui\event\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use msui\Main;
use msui\Pay;
use metowa1227\moneysystem\api\core\API;

class JoinPlayer extends Pay implements Listener
{
	public function whenJoin(PlayerJoinEvent $event)
	{
		$player = $event->getPlayer();
		if (isset($this->getDonation()[$player->getName()])) {
			$donation = $this->getDonation()[$player->getName()];
			API::getInstance()->increase($player, $donation["amount"], "Payによる寄付");
			$player->sendMessage(Main::getMessage("pay.receive", [$donation["from"], API::getInstance()->getUnit(), $donation["amount"]]));
			$this->removeDonation($player);
		}
	}
}
