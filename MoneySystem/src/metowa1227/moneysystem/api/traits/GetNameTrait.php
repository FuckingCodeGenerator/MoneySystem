<?php
namespace metowa1227\moneysystem\api\traits;

use pocketmine\Player;

trait GetNameTrait
{
    /**
     * プレイヤーオブジェクトなら名前を取得して設定します
     *
     * @param string|Player $player
     * @return void
     */
    public function getName(&$player): void
    {
        if ($player instanceof Player) {
            $player = $player->getName();
        }
    }
}
