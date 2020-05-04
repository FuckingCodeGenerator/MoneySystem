<?php
namespace metowa1227\msland\event;

use InvalidArgumentException;
use metowa1227\msland\land\LandManager;
use pocketmine\Player;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use metowa1227\msland\Main;

class PlayerLandEditEvent implements Listener
{
    public function onBreak(BlockBreakEvent $event)
    {
        if (!$this->checkPermission($event->getPlayer(), $event)) {
            $event->setCancelled();
        }
    }

    public function onPlace(BlockPlaceEvent $event)
    {
        if (!$this->checkPermission($event->getPlayer(), $event)) {
            $event->setCancelled();
        }
    }

    private function checkPermission(Player $player, Event $event): bool
    {
        if ($event instanceof BlockBreakEvent || $event instanceof BlockPlaceEvent) {
            $block = $event->getBlock();
        } else {
            throw new InvalidArgumentException("Invalid argument to land edit event processing function");
        }

        if ($player->isOp()) {
            return true;
        }

        $landManager = Main::getInstance()->getLandManager();
        $land = $landManager->getLandByPosition($block);
        $levelName = $block->getLevel()->getFolderName();

        if ($land === null) {
            if (!in_array($levelName, Main::getInstance()->getConfigArgs()["freely-editable-world"])) {
                $player->sendPopup(Main::getMessage("requires-land-purchase"));
                return false;
            }
        } else {
            if ($landManager->isPublicPlace($land[LandManager::ID])) {
                return true;
            }
            if ($land[LandManager::Owner] !== $player->getName()) {
                if (!in_array($player->getName(), $land[LandManager::Invitee])) {
                    $player->sendPopup(Main::getMessage("land-has-been-purchased", [$land[LandManager::Owner], $land[LandManager::ID]]));
                    return false;
                }
            }
        }

        return true;
    }
}
