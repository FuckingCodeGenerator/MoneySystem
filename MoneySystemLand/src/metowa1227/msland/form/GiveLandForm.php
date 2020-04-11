<?php
namespace metowa1227\msland\form;

use pocketmine\Player;
use pocketmine\Server;
use metowa1227\msland\form\selector\LandSelector;
use metowa1227\msland\form\selector\PlayerSelector;
use metowa1227\msland\jojoe77777\FormAPI\ModalForm;
use metowa1227\msland\Main;

class GiveLandForm
{
    public static function getFunc(): callable
    {
        return function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }

            $landId = LandSelector::getLandIdFromResult($player, $data);
            $form = new PlayerSelector(self::getFunc2($landId));
            $form->showUi($player);
        };
    }

    private static function getFunc2(int $landId): callable
    {
        return function (Player $player, ?string $data) use ($landId) {
            if ($data === null) {
                return;
            }

            $target = Server::getInstance()->getOfflinePlayer($data);
            if (Main::getInstance()->getConfigArgs()["limit"] !== -1) {
                if (\count(Main::getInstance()->getLandManager()->getLands($target)) >= Main::getInstance()->getConfigArgs()["limit"]) {
                    $player->sendMessage(Main::getMessage("land-limit-give"));
                    return false;
                }
            }
        
            $form = new ModalForm(self::getFunc3($data, $landId));
            $form->setTitle("Give Land");
            $form->setContent(Main::getMessage("give-land-content", [$data]));
            $form->setButton1(Main::getMessage("yes"));
            $form->setButton2(Main::getMessage("no"));
            $form->sendToPlayer($player);
        };
    }

    private static function getFunc3(string $target, int $landId): callable
    {
        return function (Player $player, bool $data) use ($target, $landId) {
            if ($data) {
                Main::getInstance()->getLandManager()->changeOwner($target, $landId);
                $player->sendMessage(Main::getMessage("give-success"));
            }
        };
    }
}