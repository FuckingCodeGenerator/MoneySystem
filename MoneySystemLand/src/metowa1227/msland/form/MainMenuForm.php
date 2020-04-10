<?php
namespace metowa1227\msland\form;

use metowa1227\msland\commands\SecondPosSetCommand;
use metowa1227\msland\form\invite\InviteForm;
use metowa1227\msland\form\selector\LandSelector;
use pocketmine\Player;
use metowa1227\msland\jojoe77777\FormAPI\SimpleForm;
use metowa1227\msland\land\BuyLandProcess;
use metowa1227\msland\Main;
use pocketmine\utils\TextFormat;

class MainMenuForm
{
    /**
     * メインメニューのボタン名のキー
     *
     * @var array
     */
    private static $buttons = [
        "button-teleport",  // テレポート
        "button-give",      // 譲渡
        "button-sell",      // 売却
        "button-invite"     // 招待
    ];

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

            // 土地の購入
            if ($data === 0) {
                if (($process = BuyLandProcess::getProcess($player)) === null) {
                    $player->sendMessage(Main::getMessage("range-of-land-not-set"));
                    return;
                } else if (!(new SecondPosSetCommand(Main::getInstance()))->checkCanBuy($process, $player)) {
                    return;
                } else if ($process->getSecondPos() === null) {
                    $player->sendMessage(Main::getMessage("second-pos-not-set"));
                    return false;
                } else {
                    $process->buyLand($player, Main::getInstance());
                }
                return;
            }

            $buttonStr = self::$buttons[$data - 1];

            switch ($buttonStr) {
                // テレポート
                case "button-teleport":
                    TeleportForm::createUi($player);
                break;
                // 譲渡
                case "button-give":
                    $form = new LandSelector(GiveLandForm::getFunc());
                    $form->showUi($player);
                break;
                // 売却
                case "button-sell":
                    $form = new LandSelector(SellForm::getFunc());
                    $form->showUi($player);
                break;
                // 招待
                case "button-invite":
                    $form = new LandSelector(InviteForm::getFunc());
                    $form->showUi($player);
                break;
            }
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
        $form = new SimpleForm(self::getFunc());
        $form->setTitle(Main::getInstance()->getDescription()->getFullName());
        
        $buyButtonColor = (($process = BuyLandProcess::getProcess($player)) === null || $process->getSecondPos() === null) ? TextFormat::DARK_RED : TextFormat::DARK_GREEN;
        $form->addButton($buyButtonColor . Main::getMessage("button-buy"));
        foreach (self::$buttons as $button) {
            $form->addButton(Main::getMessage($button));
        }

        $form->sendToPlayer($player);
    }
}
