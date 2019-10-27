<?php
namespace metowa1227\msshop\event;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use metowa1227\msshop\Main;

/**
 * 看板が破壊されたときに発生するイベントのハンドラ
 */
class SignBreakEventHandler implements Listener
{
    /** @var Main */
    private $owner;

    public function __construct(Main $owner)
    {
        $this->owner = $owner;
    }

    public function handleEvent(BlockBreakEvent $event): void
    {
        $shopData = Main::getShopData();
        $posStr = $this->owner->posToString($event->getBlock());
        // SHOP 看板でない場合
        if (!isset($shopData[$posStr])) return;

        $player = $event->getPlayer();
        // 看板を破壊する権限があるか
        if (!$player->isOp()) {
            $event->setCancelled();
            return;
        }

        unset($shopData[$posStr]);
        Main::setShopData($shopData);

        $player->sendMessage(Main::getMessage('destroyed-sign'));
    }
}