<?php
namespace metowa1227\msland\form;

use pocketmine\Player;
use metowa1227\msland\land\LandManager;

class FirstSelectModalForm
{
    /**
     * フォームのコールバック関数を取得
     *
     * @return callable
     */
    public static function getFunc(BuyForm $buyForm): callable
    {
        return function (Player $player, bool $data) use ($buyForm) {
            // 購入しない場合
            if (!$data) {
                return;
            }
            // 土地購入が可能か
            $process = $buyForm->getBuyLandProcess();
            $firstPos = $process->getFirstPos();
            $secondPos = $process->getSecondPos();
            if (($land = $buyForm->getLandManager()->existLandOwner($firstPos, $secondPos, $firstPos->level)) !== null) {
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

            // 購入確認画面
            $process->getBuyForm()->sendForm(BuyForm::FORM_BUY_CONFIRM, $player);
        };
    }
}
