<?php
namespace MoneySystemJob\event;

use pocketmine\event\{
	Listener,
	block\BlockBreakEvent
};

use MoneySystemJob\event\JobEvent;

class BlockBreak extends JobEvent implements Listener
{
	public function onBreak(BlockBreakEvent $ev) : void
	{
		JobEvent::processEvent($ev->getPlayer(), $ev->getBlock(), "Break");
	}
}
