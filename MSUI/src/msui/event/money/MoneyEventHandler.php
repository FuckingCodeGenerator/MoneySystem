<?php
namespace msui\event\money;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use metowa1227\moneysystem\event\money\MoneyChangeEvent;
use msui\Main;

class MoneyEventHandler implements Listener
{
	public function whenChange(MoneyChangeEvent $event)
	{
		if ($event->getAmount() <= Main::getConfigData()["amount-of-money-required-for-recording"]) {
			return 0;
		}

		switch ($event->getType()) {
			case MoneyChangeEvent::TYPE_INCREASE:
				$type = TextFormat::DARK_GREEN . "増加" . TextFormat::RESET;
				break;

			case MoneyChangeEvent::TYPE_REDUCE:
				$type = TextFormat::DARK_RED . "減少" . TextFormat::RESET;
				break;

			case MoneyChangeEvent::TYPE_SET:
				$type = TextFormat::DARK_PURPLE . "設定" . TextFormat::RESET;
				break;
		}

		$executor = $event->getPlayer();
		if ($executor instanceof Player) {
			$executorLevel = $executor->getLevel()->getFolderName();
		} else {
			$executorLevel = "ゲーム外";
		}

		$target = Server::getInstance()->getPlayer($event->getUser());
		if ($target !== null) {
			$targetLevel = $target->getLevel()->getFolderName();
		} else {
			$targetLevel = "ゲーム外";
		}

		Main::addHistory($this, [
			"date" => date("l Y/m/d"),
			"time" => date("H:i:s"),
			"executor" => $event->getExecutor(),
			"target" => $event->getUser(),
			"type" => $type,
			"amount" => $event->getAmount(),
			"before" => $event->getBefore(),
			"target_world" => $targetLevel,
			"executor_world" => $executorLevel
		]);
	}
}
