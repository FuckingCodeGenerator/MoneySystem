<?php
namespace metowa1227\msland\form;

use pocketmine\Player;
use metowa1227\msland\Main;
use metowa1227\msland\jojoe77777\FormAPI\ModalForm;
use metowa1227\msland\land\LandManager;
use metowa1227\moneysystem\api\core\API;
use metowa1227\msland\form\selector\LandSelector;

class SellForm
{
    public static function getFunc(): callable
    {
        return function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }

            $landId = LandSelector::getLandIdFromResult($player, $data);
            $sellPrice = (int) Main::getInstance()->getLandManager()->getLandById($landId)[LandManager::Price] / 2;
            $form = new ModalForm(self::getFunc2($landId, $sellPrice));
            $form->setTitle("Confirm Sale");
            $form->setContent(Main::getMessage("sellform-sell-confirm-content", [API::getInstance()->getUnit(), $sellPrice]));
            $form->setButton1(Main::getMessage("sellform-sell-confirm-button1"));
            $form->setButton2(Main::getMessage("sellform-sell-confirm-button2"));
            $form->sendToPlayer($player);
        };
    }

    private static function getFunc2(int $landId, int $sellPrice): callable
    {
        return function (Player $player, bool $data) use ($landId, $sellPrice) {
            if ($data) {
                if (!Main::getInstance()->getLandManager()->removeLamd($landId)) {
                    $player->sendMessage(Main::getMessage("sell-failed"));
                    return;
                }
                // 悪用される恐れがあるので処理を分割
                if (!API::getInstance()->increase($player, $sellPrice, "MSLand", "土地の売却")) {
                    $player->sendMessage(Main::getMessage("sell-failed"));
                    return;
                }
                $player->sendMessage(Main::getMessage("sell-success"));
            }
        };
    }
}
