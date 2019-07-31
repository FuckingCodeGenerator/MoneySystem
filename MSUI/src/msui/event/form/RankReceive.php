<?php
namespace msui\event\form;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\CustomForm;
use metowa1227\moneysystem\api\core\API;
use msui\Main;

class RankReceive
{
	public function receiveResponse(Player $player, bool $data)
	{
		$api = API::getInstance();
        $rank = 1;
        $all = $api->getAll();
        arsort($all);
        $form = new CustomForm(null);
        $form->setTitle(TextFormat::DARK_GREEN . "MoneySystem Rank");
        $form->addLabel(Main::getMessage("form.rank.result"));
        foreach ($all as $name => $money) {
            if ($data && Server::getInstance()->isOp($name)) {
            	continue;
            }
            if ($player->getName() === $name) {
                $form->addLabel(TextFormat::AQUA . $rank . " | " . $name . " >>  " . TextFormat::YELLOW . $api->getUnit() . $money);
            } else {
                $form->addLabel(TextFormat::WHITE . $rank . " | " . $name . " >>  " . TextFormat::YELLOW . $api->getUnit() . $money);
            }
            $rank++;
		}
		$form->sendToPlayer($player);
	}
}
