<?php
namespace msui\event\form;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\SimpleForm;
use metowa1227\moneysystem\api\core\API;
use msui\Main;

class SeeReceive
{
	/** @var array */
	private $onlineList = [], $searchResult = [];

	public function __construct(array $onlines)
	{
		$this->onlineList = $onlines;
	}

	public function receiveResponse(Player $player, ?array $data)
	{
		if ($data === null) {
			return 0;
		}
		$api = API::getInstance();

		if ($this->onlineList[$data[1]] !== Main::getMessage("form.see.dropdown.default")) {
			$player->sendMessage(Main::getMessage("see.result", [$this->onlineList[$data[1]], $api->getUnit(), $api->get($this->onlineList[$data[1]])]));
			return 0;
		}

		if ($data[2] === "") {
			return 0;
		}

		$form = new SimpleForm([$this, "receiveSelectedPlayer"]);
		$form->setTitle(TextFormat::DARK_GREEN . "MoneySystem See");
		$form->setContent(Main::getMessage("form.see.search.result"));
		$result = [];
		foreach ($api->getAll() as $key => $value) {
			if (strpos($key, $data[2]) !== false) {
				$result[] = $key;
				$form->addButton(TextFormat::BLACK . str_replace($data[2], TextFormat::DARK_PURPLE . $data[2] . TextFormat::BLACK, $key));
			}
		}

		$this->searchResult = $result;
		$form->sendToPlayer($player);
		unset($this->onlineList);
		return 0;
	}

	public function receiveSelectedPlayer(Player $player, ?int $data)
	{
		if ($data === null) {
			return 0;
		}
		$api = API::getInstance();

		$player->sendMessage(Main::getMessage("see.result", [$this->searchResult[$data], $api->getUnit(), $api->get($this->searchResult[$data])]));
		unset($this->searchResult);
		return 0;
	}
}