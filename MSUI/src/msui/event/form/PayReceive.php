<?php
namespace msui\event\form;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use metowa1227\moneysystem\api\core\API;
use msui\Main;
use msui\Pay;

class PayReceive extends Pay
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
		if ($this->onlineList[$data[1]] === Main::getMessage("form.pay.dropdown.default") && $data[2] === "") {
			return 0;
		}

		// Errors
		$error = false;
		$message = "";
		$names = [Main::getMessage("form.pay.dropdown.default")];
		foreach (Server::getInstance()->getOnlinePlayers() as $online) {
			$names[] = $online->getName();
		}
		$form = new CustomForm([$this, "receiveResponse"]);
		$form->setTitle(TextFormat::DARK_GREEN . "MoneySystem Pay");
		if (!ctype_digit($data[3])) {
			$error = true;
			$message .= "\n" . Main::getMessage("pay.integer-only");
		}
		$data[3] = intval($data[3]);
		if ($data[3] < 0) {
			$error = true;
			$message .= "\n" . Main::getMessage("pay.only-avove-zero");
		}
		if ($api->get($player) < $data[3]) {
			$error = true;
			$message .= "\n" . Main::getMessage("pay.insufficient-money");
		}
		$form->addLabel($message . "\n\n" . TextFormat::WHITE . Main::getMessage("form.pay.label"));
		$form->addDropdown(Main::getMessage("form.pay.dropdown"), $names);
		$form->addInput(Main::getMessage("form.pay.input.playername"));
		$form->addInput(Main::getMessage("form.pay.input.amount"));

		if ($error) {
			$this->onlineList = $names;
			$form->sendToPlayer($player);
			return 0;
		}

		unset($form, $error);

		if ($this->onlineList[$data[1]] !== Main::getMessage("form.pay.dropdown.default")) {
			$this->pay($player, $this->onlineList[$data[1]], $data[3]);
			return 0;
		}

		$form = new SimpleForm([$this, "receiveSelectedPlayer"]);
		$form->setTitle(TextFormat::DARK_GREEN . "MoneySystem Pay");
		$form->setContent(Main::getMessage("form.pay.search.result"));
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
		$api = API::getInstance();

		$this->pay($player, $this->searchResult[$data], $this->amount);
		unset($this->searchResult, $this->amount);
		return 0;
	}

	private function pay(Player $from, string $to, int $amount)
	{
		$api = API::getInstance();
		$api->reduce($from, $amount, $from->getName(), "Payによる寄付");
		// offline
		if (($target = (Server::getInstance()->getPlayer($to))) === null || $target->getName() !== $to) {
			$this->addDonation($from, $to, $amount);
		} else {
			$api->increase($to, $amount, $from->getName(), "Payによる寄付");
			$target->sendMessage(Main::getMessage("pay.receive", [$from->getName(), $api->getUnit(), $amount]));
		}
		$from->sendMessage(Main::getMessage("pay.success", [$to, $api->getUnit(), $amount]));
	}
}
