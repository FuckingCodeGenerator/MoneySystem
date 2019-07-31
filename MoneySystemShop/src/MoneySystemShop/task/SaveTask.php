<?php
namespace MoneySystemShop\task;

use pocketmine\scheduler\Task;

use MoneySystemShop\MoneySystemShop as Main;

class SaveTask extends Task
{
	public function __construct($file, Main $main)
	{
		$this->file = $file;
		$this->main = $main;
	}

	public function onRun(int $tick) : void
	{
		$this->file->setAll(Main::$shop);
		$this->file->save();
	}
}