<?php
namespace metowa1227\moneysystem\api\traits;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;

trait GetNameTrait
{
    /**
     * プレイヤーオブジェクトなら名前を取得して設定します
     *
     * @param string|Player|ConsoleCommandSender|OfflinePlayer $player
     * @return void
     */
    public function getName(&$player): void
    {
        if ($player instanceof Player || $player instanceof ConsoleCommandSender || $player instanceof OfflinePlayer) {
            $player = $player->getName();
        }
    }
}
