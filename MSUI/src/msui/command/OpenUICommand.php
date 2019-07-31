<?php
namespace msui\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use metowa1227\moneysystem\api\core\API;
use msui\Main;
use msui\event\form\MainUIReceive;
use jojoe77777\FormAPI\SimpleForm;

class OpenUICommand extends Command
{
	/** @var Main */
	private $owner;

	public function __construct(Main $owner)
	{
        parent::__construct("msys", "MoneySystemのフォームを開く", "/msys");
        $this->setPermission("msui.command.openui");
        $this->owner = $owner;
	}

	public function execute(CommandSender $sender, string $label, array $args) : bool
	{
		$owner = $this->owner;
		if (!$sender instanceof Player) {
			$sender->sendMessage($owner->getMessage("only-in-game"));
			return true;
		}

		$form = new SimpleForm([new MainUIReceive, "receiveResponse"]);
		$form->setTitle(TextFormat::DARK_GREEN . "MoneySystem");
		$form->setContent($owner->getMessage("form.menu.info", $this->getInfo($sender)));
		$form->addButton($owner->getMessage("see"));
		$form->addButton($owner->getMessage("pay"));
		$form->addButton($owner->getMessage("ranking"));
		$form->addButton($owner->getMessage("all"));
		$form->addButton($owner->getMessage("set"));
		$form->addButton($owner->getMessage("increase"));
		$form->addButton($owner->getMessage("reduce"));
		$form->addButton($owner->getMessage("history"));

		$form->sendToPlayer($sender);
		return true;
	}

	private function getInfo(Player $player) : array
	{
		$result = [];
		$api = API::getInstance();
		$name = $player->getName();

		// ステータス算出のための全所持金の合計を計算
        $allMoney = 0;
        foreach ($api->getAll() as $key => $value) {
            if (Server::getInstance()->isOp($key)) {
                continue;
            }
            $allMoney += $value;
        }

        // ステータスを計算
        $status = 0;
        if ($allMoney > 0) {
            $status = round((($api->get($name) / $allMoney) * 100), 2);
        }

        // ランキングを計算
        $moneys = [];
        foreach ($api->getAll() as $key => $value) {
            $moneys[] = $value;
        }
        rsort($moneys);
        foreach ($moneys as $key => $value) {
            if ($value == $api->get($name)) {
                $rank = $key + 1;
            }
        }
        $all = count($moneys);

		$result[] = $api->getUnit();
		$result[] = $api->get($player);
		$result[] = $status;
		$result[] = $rank;
		$result[] = $all;

		return $result;
	}
}
