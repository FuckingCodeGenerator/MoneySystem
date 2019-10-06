<?php
namespace metowa1227\moneysystem\task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use metowa1227\moneysystem\Main;

/**
 * 自動セーブのリピーティングタスク
 */
class SaveTask extends Task
{
	/**
	 * Undocumented function
	 *
	 * @param Main $main
	 * @param boolean $announce 自動セーブ時にアナウンスするかどうか
	 */
	public function __construct(Main $main, bool $announce)
	{
		$this->owner = $main;
		$this->announce = $announce;
	}

	/**
	 * 自動セーブを実行
	 *
	 * @param integer $tick
	 * @return void
	 */
	public function onRun(int $tick): void
	{
		$api = $this->owner->getAPI();
		$result = $this->owner->getAPI()->save();

		if ($this->announce) {
			Server::getInstance()->broadcastMessage("[MoneySystem] " . $api->getMessage("autosave-start"));
			if ($result) {
				Server::getInstance()->broadcastMessage("[MoneySystem] " . $api->getMessage("autosave-success"));
			} else {
				Server::getInstance()->broadcastMessage("[MoneySystem] " . $api->getMessage("autosave-failed"));
			}
		}
	}
}
