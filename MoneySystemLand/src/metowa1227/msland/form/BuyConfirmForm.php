<?php
namespace metowa1227\msland\form;

use pocketmine\Player;
use metowa1227\msland\land\LandManager;
use metowa1227\msland\land\BuyLandProcess;
use metowa1227\moneysystem\api\core\API;

class BuyConfirmForm
{
    /**
     * フォームのコールバック関数を取得
     *
     * @return callable
     */
    public static function getFunc(BuyForm $buyForm): callable
    {
        $api = API::getInstance();
        return function (Player $player, bool $data) use ($buyForm, $api) {
            // 購入しない場合
            if (!$data) {
                return;
            }
            // 所持金が足りているか
            if ($api->get($player) < $buyForm->getPrice()) {
                $player->sendMessage($buyForm->getOwner()->getMessage("lack-money", [$api->getUnit(), $buyForm->getPrice() - $api->get($player)]));
                return;
            }
            if (!$api->reduce($player, $buyForm->getPrice(), $buyForm->getOwner()->getName(), "土地の購入")) {
                $player->sendMessage($buyForm->getOwner()->getMessage("buy-land-failed"));
                return;
            }
            // 土地購入が可能か
            if (($land = $buyForm->getLandManager()->addLand($player, $buyForm->getBuyLandProcess())) !== true) {
                // 既に購入者がいた場合
                if (is_array($land)) {
                    $player->sendMessage($buyForm->getOwner()->getMessage("land-already-bought", [
                        $land[LandManager::Owner],
                        $land[LandManager::ID]
                    ]));
                    return;
                }
                // 土地の範囲が2つとも選択されていない場合
                $player->sendMessage($buyForm->getOwner()->getMessage("land-range-not-set"));
                return;
            }
            $player->sendMessage($buyForm->getOwner()->getMessage("bought-land"));
            BuyLandProcess::unsetProcess($player);
        };
    }
}
