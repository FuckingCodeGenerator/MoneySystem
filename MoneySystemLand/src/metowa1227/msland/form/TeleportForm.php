<?php
namespace metowa1227\msland\form;

use pocketmine\Player;
use metowa1227\msland\Main;
use metowa1227\msland\jojoe77777\FormAPI\SimpleForm;
use metowa1227\msland\land\LandManager;
use metowa1227\msland\form\AddTeleportDestination\AddTeleportDestinationForm;
use metowa1227\msland\teleport\LandTeleporter;

class TeleportForm
{
    /**
     * フォームのコールバック関数を取得
     *
     * @return callable
     */
    private static function getFunc(): callable
    {
        return function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }

            // テレポート先の追加
            if ($data === 0) {
                AddTeleportDestinationForm::createUi($player);
                return;
            }

            $landManager = Main::getInstance()->getLandManager();
            $lands = $landManager->getLands($player);
            if ($data-- > \count($lands)) {
                $selectedLand = $landManager->getTeleportList($player)[$data - \count($lands)];
            } else {
                $selectedLand = $lands[$data];
            }
            LandTeleporter::teleportToLand($player, $selectedLand);
        };
    }

    /**
     * フォームを表示
     *
     * @param Main   $owner
     * @param Player $playuer
     * @return void
     */
    public static function createUi(Player $player): void
    {
        $landManager = Main::getInstance()->getLandManager();
        $form = new SimpleForm(self::getFunc());
        $form->setTitle("MSLand - Teleport");
        $form->setContent(Main::getMessage("teleport-content"));
        
        $form->addButton(Main::getMessage("teleport-add-destination"));
        // 自身が所有する土地
        foreach ($landManager->getLands($player) as $land) {
            $form->addButton(Main::getMessage("teleport-list-own-land", [$land[LandManager::ID]]));
        }
        // 追加した土地
        foreach ($landManager->getTeleportList($player) as $tpDist) {
            $form->addButton(Main::getMessage("teleport-list-added-land", [$tpDist[LandManager::Owner], $tpDist[LandManager::ID]]));
        }

        $form->sendToPlayer($player);
    }
}
