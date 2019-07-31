<?php
namespace MoneySystemJob\event;

use pocketmine\event\{
	Listener,
	block\BlockPlaceEvent
};

use MoneySystemJob\event\JobEvent;

class BlockPlace extends JobEvent implements Listener
{
	public function onPlace(BlockPlaceEvent $ev) : void
	{
		JobEvent::processEvent($ev->getPlayer(), $ev->getBlock(), "Place");
	}
}
