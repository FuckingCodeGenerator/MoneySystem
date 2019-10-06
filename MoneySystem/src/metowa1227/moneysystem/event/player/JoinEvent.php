<?php
namespace metowa1227\moneysystem\event\player;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use metowa1227\moneysystem\api\core\API;

/**
 * プレイヤーがサーバーに参加したときに発生するイベントハンドラ
 */
class JoinEvent implements Listener
{
    public function onJoin(PlayerJoinEvent $event)
    {
        // アカウントを作成する
        // 関数側でアカウントが存在するかなどの処理をしてくれるので丸投げ
        API::getInstance()->createAccount($event->getPlayer());
    }
}
