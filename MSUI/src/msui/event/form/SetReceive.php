<?php
namespace msui\event\form;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use metowa1227\moneysystem\api\core\API;
use msui\Main;

class SetReceive
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
		if ($this->onlineList[$data[1]] === Main::getMessage("form.set.dropdown.default") && $data[2] === "") {
			return 0;
		}

		// Errors
		$error = false;
		$message = "";
		$names = [Main::getMessage("form.set.dropdown.default")];
		foreach (Server::getInstance()->getOnlinePlayers() as $online) {
			$names[] = $online->getName();
		}
		$form = new CustomForm([$this, "receiveResponse"]);
		$form->setTitle(TextFormat::DARK_RED . "MoneySystem Set [OPERATORS ONLY]");
		if (!ctype_digit($data[3])) {
			$error = true;
			$message .= "\n" . Main::getMessage("set.integer-only");
		}
		$data[3] = intval($data[3]);
		if ($data[3] < 0) {
			$error = true;
			$message .= "\n" . Main::getMessage("set.only-avove-zero");
		}
		$form->addLabel($message . "\n\n" . TextFormat::WHITE . Main::getMessage("form.set.label"));
		$form->addDropdown(Main::getMessage("form.set.dropdown"), $names);
		$form->addInput(Main::getMessage("form.set.input.playername"));
		$form->addInput(Main::getMessage("form.set.input.amount"));

		if ($error) {
			$this->onlineList = $names;
			$form->sendToPlayer($player);
			return 0;
		}

		unset($form, $error);

		if ($this->onlineList[$data[1]] !== Main::getMessage("form.set.dropdown.default")) {
			$api->set($this->onlineList[$data[1]], $data[3], $player->getName());
			$player->sendMessage(Main::getMessage("set.success", [$this->onlineList[$data[1]], $api->getUnit(), $data[3]]));
			return 0;
		}

		$form = new SimpleForm([$this, "receiveSelectedPlayer"]);
		$form->setTitle(TextFormat::DARK_RED . "MoneySystem Set [OPERATORS ONLY]");
		$form->setContent(Main::getMessage("form.set.search.result"));
		$result = [];
		foreach ($api->getAll() as $key => $value) {
			if (strpos($key, $data[2]) !== false) {
				$result[] = $key;
				$form->addButton(TextFormat::BLACK . str_replace($data[2], TextFormat::DARK_PURPLE . $data[2] . TextFormat::BLACK, $key));
			}
		}

		$this->searchResult = $result;
		$this->amount = $data[3];
		$form->sendToPlayer($player);
		unset($this->onlineList);
		return 0;
	}

	public function receiveSelectedPlayer(Player $player, ?int $data)
	{
		if ($data === null) {
			return 0;
		}

		API::getInstance()->set($this->searchResult[$data], $this->amount, $player->getName());
		$player->sendMessage(Main::getMessage("set.success", [$this->searchResult[$data], API::getInstance()->getUnit(), $this->amount]));
		unset($this->searchResult, $this->amount);
		return 0;
	}
}
