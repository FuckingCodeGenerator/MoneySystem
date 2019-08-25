<?php
namespace msui\event\form;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use metowa1227\moneysystem\api\core\API;
use msui\jojoe77777\FormAPI\CustomForm;
use msui\Main;

class HistoryReceive
{
	/** @var array */
	private $history = [];

	public function __construct(array $history)
	{
		$this->history = $history;
	}

	public function receiveResponse(Player $player, ?int $data)
	{
		if ($data === null || $data === 0) {
			return 0;
		}

		$this->history = $this->history[--$data];
		$form = new CustomForm([$this, "receiveUndo"]);
		$form->setTitle(TextFormat::DARK_RED . "MoneySystem History [OPERATORS ONLY]");
		$form->addLabel(Main::getMessage("form.history.result", array_values($this->history)));
		$form->addToggle(Main::getMessage("form.history.undo"));

		$form->sendToPlayer($player);
		return 0;
	}

	public function receiveUndo(Player $player, ?array $data)
	{
		$api = API::getInstance();
		if ($data[1]) {
			$api->set($this->history["target"], $this->history["before"], "操作の取り消し");
			$player->sendMessage(Main::getMessage("form.history.undo.success"));
		}
		unset($this->history);
	}
}
