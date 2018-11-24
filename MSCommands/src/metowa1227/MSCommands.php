<?php
namespace metowa1227;

use pocketmine\plugin\PluginBase;

use metowa1227\command\MainCommand;

class MSCommands extends PluginBase
{
	public function onEnable()
	{
		$this->initCommand();
		if (empty($this->getServer()->getPluginManager()->getPlugin("MoneySystem")))
			$this->getServer()->getPluginManager()->disablePlugin($this);
	}

	private function initCommand() : void
	{
		$this->getServer()->getCommandMap()->register("money", new MainCommand());
	}
}
