<?php
namespace metowa1227\msland\teleport;

use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\level\Position;
use metowa1227\msland\land\LandManager;
use metowa1227\msland\Main;

class LandTeleporter
{
    public static function teleportToLand(Player $player, array $land): void
    {
        if (!Main::getInstance()->getConfigArgs()["teleport"]) {
            if (!$player->isOp()) {
                $player->sendMessage(Main::getMessage("teleport-is-not-enabled"));
                return;
            }
        }
        if (Main::getInstance()->getConfigArgs()["teleport-only-op-allowed"]) {
            if (!$player->isOp()) {
                $player->sendMessage(Main::getMessage("unauthorized-teleport"));
                return;
            }
        }
        $minMax = Main::getInstance()->getLandManager()->getMinMaxVec($land);
        $centerX = $minMax[LandManager::X_MAX] - (($minMax[LandManager::X_MAX] - $minMax[LandManager::X_MIN]) / 2);
        $centerZ = $minMax[LandManager::Z_MAX] - (($minMax[LandManager::Z_MAX] - $minMax[LandManager::Z_MIN]) / 2);
        $level = Main::getInstance()->getServer()->getLevelByName($land[LandManager::Level]);
        
        $loopFlag = true;
        $airFound = false;
        while ($loopFlag) {
            for ($y = $minMax[LandManager::Y_MIN]; $y < $minMax[LandManager::Y_MAX] + 1; $y++) {
                if ($level->getBlockAt($centerX, $y, $centerZ)->getId() === Block::AIR) {
                    if (!$airFound) {
                        $airFound = true;
                    } else {
                        $loopFlag = false;
                        break;
                    }
                } else {
                    $airFound = false;
                }
            }
            if ($loopFlag) {
                if (++$centerX > $minMax[LandManager::X_MAX]) {
                    $centerX--;
                    if (++$centerZ > $minMax[LandManager::Z_MAX]) {
                        $centerZ--;
                        if (--$centerX < $minMax[LandManager::X_MIN]) {
                            $centerX++;
                            if (--$centerZ < $minMax[LandManager::Z_MIN]) {
                                $centerZ++;
                                $player->sendMessage(Main::getMessage("teleport-failed"));
                                return;
                            }
                        }
                    }
                }
            }
        }

        $player->teleport(new Position($centerX, $y - 0.5, $centerZ, $level));
        $player->sendMessage(Main::getMessage("teleport-success"));
    }
}
