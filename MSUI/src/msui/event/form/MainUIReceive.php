<?php
namespace msui\event\form;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use metowa1227\moneysystem\api\core\API;
use msui\jojoe77777\FormAPI\CustomForm;
use msui\jojoe77777\FormAPI\ModalForm;
use msui\jojoe77777\FormAPI\SimpleForm;
use msui\Main;

class MainUIReceive
{
	/** @var int */
	const MENU_SEE = 0;
	const MENU_PAY = 1;
	const MENU_RANK = 2;
	const MENU_ALL = 3;
	const MENU_SET = 4;
	const MENU_INCREASE = 5;
	const MENU_REDUCE = 6;
	const MENU_HISTORY = 7;

	public function receiveResponse(Player $player, ?int $data)
	{
		if ($data === null) {
			return 0;
		}

		switch ($data) {
			case self::MENU_SEE:
				$names = [Main::getMessage("form.see.dropdown.default")];
				foreach (Server::getInstance()->getOnlinePlayers() as $online) {
					$names[] = $online->getName();
				}
				$form = new CustomForm([new SeeReceive($names), "receiveResponse"]);
				$form->setTitle(TextFormat::DARK_GREEN . "MoneySystem See");
				$form->addLabel(Main::getMessage("form.see.label"));
				$form->addDropdown(Main::getMessage("form.see.dropdown"), $names);
				$form->addInput(Main::getMessage("form.see.input"));

				$form->sendToPlayer($player);
				return 0;

			case self::MENU_PAY:
				$names = [Main::getMessage("form.pay.dropdown.default")];
				foreach (Server::getInstance()->getOnlinePlayers() as $online) {
					$names[] = $online->getName();
				}
				$form = new CustomForm([new PayReceive($names), "receiveResponse"]);
				$form->setTitle(TextFormat::DARK_GREEN . "MoneySystem Pay");
				$form->addLabel(Main::getMessage("form.pay.label"));
				$form->addDropdown(Main::getMessage("form.pay.dropdown"), $names);
				$form->addInput(Main::getMessage("form.pay.input.playername"));
				$form->addInput(Main::getMessage("form.pay.input.amount"));

				$form->sendToPlayer($player);
				return 0;

			case self::MENU_RANK:
				$form = new ModalForm([new RankReceive, "receiveResponse"]);
				$form->setTitle(TextFormat::DARK_GREEN . "MoneySystem Rank");
				$form->setContent(Main::getMessage("form.rank.modal.content"));
				$form->setButton1(Main::getMessage("form.rank.modal.button1"));
				$form->setButton2(Main::getMessage("form.rank.modal.button2"));

				$form->sendToPlayer($player);
				return 0;

			case self::MENU_ALL:
				$api = API::getInstance();
		        $form = new CustomForm(null);
		        $form->setTitle(TextFormat::DARK_GREEN . "MoneySystem AllData");
		        foreach ($api->getAll() as $name => $money) {
		        	$tag = (Server::getInstance()->isOp($name)) ? TextFormat::RED . " [OP]" : "";
		            $form->addLabel(TextFormat::WHITE . $name . " >>  " . TextFormat::YELLOW . $api->getUnit() . $money . $tag . "\n");
				}
				$form->sendToPlayer($player);
				return 0;

			case self::MENU_SET:
				if (!Server::getInstance()->isOp($player->getName())) {
					$player->sendMessage(Main::getMessage("no-permission"));
					return 0;
				}

				$names = [Main::getMessage("form.set.dropdown.default")];
				foreach (Server::getInstance()->getOnlinePlayers() as $online) {
					$names[] = $online->getName();
				}
				$form = new CustomForm([new SetReceive($names), "receiveResponse"]);
				$form->setTitle(TextFormat::DARK_RED . "MoneySystem Set [OPERATORS ONLY]");
				$form->addLabel(Main::getMessage("form.set.label"));
				$form->addDropdown(Main::getMessage("form.set.dropdown"), $names);
				$form->addInput(Main::getMessage("form.set.input.playername"));
				$form->addInput(Main::getMessage("form.set.input.amount"));

				$form->sendToPlayer($player);
				return 0;

			case self::MENU_INCREASE:
				if (!Server::getInstance()->isOp($player->getName())) {
					$player->sendMessage(Main::getMessage("no-permission"));
					return 0;
				}

				$names = [Main::getMessage("form.increase.dropdown.default")];
				foreach (Server::getInstance()->getOnlinePlayers() as $online) {
					$names[] = $online->getName();
				}
				$form = new CustomForm([new IncreaseReceive($names), "receiveResponse"]);
				$form->setTitle(TextFormat::DARK_RED . "MoneySystem Increase [OPERATORS ONLY]");
				$form->addLabel(Main::getMessage("form.increase.label"));
				$form->addDropdown(Main::getMessage("form.increase.dropdown"), $names);
				$form->addInput(Main::getMessage("form.increase.input.playername"));
				$form->addInput(Main::getMessage("form.increase.input.amount"));

				$form->sendToPlayer($player);
				return 0;

			case self::MENU_REDUCE:
				if (!Server::getInstance()->isOp($player->getName())) {
					$player->sendMessage(Main::getMessage("no-permission"));
					return 0;
				}

				$names = [Main::getMessage("form.reduce.dropdown.default")];
				foreach (Server::getInstance()->getOnlinePlayers() as $online) {
					$names[] = $online->getName();
				}
				$form = new CustomForm([new ReduceReceive($names), "receiveResponse"]);
				$form->setTitle(TextFormat::DARK_RED . "MoneySystem Reduce [OPERATORS ONLY]");
				$form->addLabel(Main::getMessage("form.reduce.label"));
				$form->addDropdown(Main::getMessage("form.reduce.dropdown"), $names);
				$form->addInput(Main::getMessage("form.reduce.input.playername"));
				$form->addInput(Main::getMessage("form.reduce.input.amount"));

				$form->sendToPlayer($player);
				return 0;

			case self::MENU_HISTORY:
				if (!Server::getInstance()->isOp($player->getName())) {
					$player->sendMessage(Main::getMessage("no-permission"));
					return 0;
				}

				$histories = Main::getHistory($this);
				$form = new SimpleForm([new HistoryReceive($histories), "receiveResponse"]);
				$form->setTitle(TextFormat::DARK_RED . "MoneySystem History [OPERATORS ONLY]");
				$form->setContent(Main::getMessage("form.history.label"));
				$form->addButton(Main::getMessage("close"));
				foreach ($histories as $key => $history) {
					$form->addButton(TextFormat::BLACK . ($key + 1) . " : " . $history["executor"] . "による所持金の" . $history["type"] . "\n" . "対象: " . $history["target"] . " 金額: " . $history["amount"]);
				}
				$form->sendToPlayer($player);
				return 0;
		}
	}
}
