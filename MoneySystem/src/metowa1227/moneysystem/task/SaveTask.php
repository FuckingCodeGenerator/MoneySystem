<?php
namespace metowa1227\moneysystem\task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use metowa1227\moneysystem\Main;

class SaveTask extends Task
{
	public function __construct(Main $main)
	{
		$this->owner = $main;
	}

	public function onRun(int $tick) : void
	{
		$api = $this->owner->getAPI();
		Server::getInstance()->broadcastMessage("[MoneySystem] " . $api->getMessage("autosave-start"));
		if ($this->owner->getAPI()->save()) {
			Server::getInstance()->broadcastMessage("[MoneySystem] " . $api->getMessage("autosave-success"));
		} else {
			Server::getInstance()->broadcastMessage("[MoneySystem] " . $api->getMessage("autosave-failed"));
		}
	}
}
