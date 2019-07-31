<?php
namespace MoneySystemSell\task;

use pocketmine\scheduler\Task;

use MoneySystemSell\MoneySystemSell as Main;

class SaveTask extends Task
{
	public function __construct($file, Main $main)
	{
		$this->file = $file;
		$this->main = $main;
	}

	public function onRun(int $tick) : void
	{
		$this->file->setAll(Main::$sell);
		$this->file->save();
	}
}