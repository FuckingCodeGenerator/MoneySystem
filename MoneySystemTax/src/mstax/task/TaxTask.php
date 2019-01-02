<?php
namespace mstax\task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use metowa1227\moneysystem\api\core\API;

class TaxTask extends Task
{
	/**
	 * @param int $tax
	 */
	public function __construct(int $tax)
	{
		$this->tax = $tax;
	}

	/**
	 * @param  int $tick
	 * @return void
	 */
	public function onRun(int $tick) : void
	{
		foreach (Server::getInstance()->getOnlinePlayers() as $online) { 
			API::getInstance()->reduce($online, $this->tax);
			$online->sendMessage("税金" . API::getInstance()->getUnit() . $this->tax . "を徴収しました。");
		}
	}
}
