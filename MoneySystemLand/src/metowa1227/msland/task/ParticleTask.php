<?php
namespace metowa1227\msland\task;

use metowa1227\msland\commands\LandParticleCommand;
use metowa1227\msland\land\LandManager;
use metowa1227\msland\Main;
use pocketmine\scheduler\Task;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\math\Vector3;

class ParticleTask extends Task
{
    /** @var array */
    private $land;
    /** @var int */
    private $y;
    /** @var int */
    private $timer;
    /** @var array */
    private $options;

    public function __construct(array $land, int $y, int $count, array $options)
    {
        $this->land = $land;
        $this->y = $y;
        $this->timer = $count;
        $this->options = $options;
    }

    public function onRun(int $tick): void
    {
        if ($this->timer === 0) {
            $this->getHandler()->cancel();
            LandParticleCommand::unsetRunningTask($this->land[LandManager::ID]);
        }
        $this->timer--;

        $minMax = Main::getInstance()->getLandManager()->getMinMaxVec($this->land);
        $level = Main::getInstance()->getServer()->getLevelByName($this->land[LandManager::Level]);
        
        if (\array_search("--all_sides", $this->options) !== false) {
            for ($i = 0; $i < 4; $i++) {
                for ($y = $minMax[LandManager::Y_MIN]; $y <= $minMax[LandManager::Y_MAX]; $y++) {
                    if ($y === $minMax[LandManager::Y_MIN] || $y === $minMax[LandManager::Y_MAX]) {
                        $this->y = $y;
                        $this->showParticles($minMax, $level);
                        continue;
                    }
                    switch ($i) {
                        case 0:
                            $x = $minMax[LandManager::X_MIN] + 1;
                            $z = $minMax[LandManager::Z_MIN] + 1;
                        break;
                        case 1:
                            $x = $minMax[LandManager::X_MAX];
                            $z = $minMax[LandManager::Z_MIN] + 1;
                        break;
                        case 2:
                            $x = $minMax[LandManager::X_MIN] + 1;
                            $z = $minMax[LandManager::Z_MAX];
                        break;
                        case 3:
                            $x = $minMax[LandManager::X_MAX];
                            $z = $minMax[LandManager::Z_MAX];
                        break;
                    }
                    $pos = new Vector3($x, $y, $z);
                    $particle = new CriticalParticle($pos);
                    $level->addParticle($particle);
                }
            }
        } else {
            $this->showParticles($minMax, $level);
        }
    }

    private function showParticles($minMax, $level)
    {
        for ($x = $minMax[LandManager::X_MIN] + 1; $x <= $minMax[LandManager::X_MAX]; $x++) {
            for ($i = 0; $i <= 1; $i++) {
                if (\array_search("--all_height", $this->options) !== false) {
                    for ($this->y = $minMax[LandManager::Y_MIN]; $this->y <= $minMax[LandManager::Y_MAX]; $this->y++) {
                        $z = $i === 0 ? $minMax[LandManager::Z_MIN] + 1 : $minMax[LandManager::Z_MAX];
                        $pos = new Vector3($x, $this->y + 0.3, $z);
                        $particle = new CriticalParticle($pos);
                        $level->addParticle($particle);
                    }
                } else {
                    $z = $i === 0 ? $minMax[LandManager::Z_MIN] + 1 : $minMax[LandManager::Z_MAX];
                    $pos = new Vector3($x, $this->y + 0.3, $z);
                    $particle = new CriticalParticle($pos);
                    $level->addParticle($particle);
                }
            }
        }
        for ($z = $minMax[LandManager::Z_MIN] + 1; $z <= $minMax[LandManager::Z_MAX]; $z++) {
            for ($i = 0; $i <= 1; $i++) {
                if (\array_search("--all_height", $this->options) !== false) {
                    for ($this->y = $minMax[LandManager::Y_MIN]; $this->y <= $minMax[LandManager::Y_MAX]; $this->y++) {
                        $x = $i === 0 ? $minMax[LandManager::X_MIN] + 1 : $minMax[LandManager::X_MAX];
                        $pos = new Vector3($x, $this->y + 0.3, $z);
                        $particle = new CriticalParticle($pos);
                        $level->addParticle($particle);
                    }
                } else {
                    $x = $i === 0 ? $minMax[LandManager::X_MIN] + 1 : $minMax[LandManager::X_MAX];
                    $pos = new Vector3($x, $this->y + 0.3, $z);
                    $particle = new CriticalParticle($pos);
                    $level->addParticle($particle);
                }
            }
        }
    }
}
