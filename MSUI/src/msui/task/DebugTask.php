<?php
namespace msui\task;

use pocketmine\scheduler\Task;
use metowa1227\moneysystem\api\core\API;

class DebugTask extends Task
{
	/** @var Player */
	private $player;

	public function __construct(\pocketmine\Player $player)
	{
		$this->player = $player;
	}

	public function onRun(int $tick)
	{
		$this->player->sendPopup("money:" . API::getInstance()->get($this->player));
	}
}
