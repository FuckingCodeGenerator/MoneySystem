<?php
namespace MoneySystemJob\task;

use pocketmine\scheduler\Task;

use MoneySystemJob\MoneySystemJob as Main;

class SaveTask extends Task
{
	public function __construct(Main $main)
	{
		$this->main = $main;
	}

	public function onRun(int $tick) : void
	{
		$this->main->save();
	}
}
