<?php
namespace metowa1227\msland\commands;

use metowa1227\msland\land\LandManager;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use metowa1227\msland\Main;
use metowa1227\msland\task\ParticleTask;

class LandParticleCommand extends Command
{
    private const CMD_COMMAND = "landparticle";
    private const CMD_DESCRIPTION = "立っている土地の境界線にパーティクルを表示";
    private const CMD_USAGE = "/landparticle [Time(s)] [Option]";

    /** @var array */
    private static $runningTasks = [];

    public static function unsetRunningTask(int $landId): void
    {
        unset(self::$runningTasks[$landId]);
    }

    public function __construct()
    {
        parent::__construct(self::CMD_COMMAND, self::CMD_DESCRIPTION, self::CMD_USAGE, ["lp"]);
        $this->setPermission("msland.command.landparticle");
    }

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if (!Main::isPlayer($sender)) {
            $sender->sendMessage(Main::getMessage("in-game-only"));
            return false;
        }

        $landManager = Main::getInstance()->getLandManager();
        $land = $landManager->getLandByPosition($sender->getPosition());
        
        if ($land === null) {
            $sender->sendMessage(Main::getMessage("land-not-found"));
            return false;
        }
        if ($land[LandManager::Owner] !== $sender->getName() && !$landManager->isInvitee($land, $sender)) {
            $sender->sendMessage(Main::getMessage("must-owner-or-invitee"));
            return false;
        }
        if (isset(self::$runningTasks[$land[LandManager::ID]])) {
            $sender->sendMessage(Main::getMessage("task-already-running"));
            return false;
        }
        if (!isset($args[0])) {
            $count = 120;
        } else {
            if (!\ctype_digit($args[0])) {
                $sender->sendMessage(Main::getMessage("enter-number"));
                return false;
            }
            $count = intval($args[0]) * 4;
        }        

        if (\array_search("--help", $args) !== false) {
            $sender->sendMessage("LandParticleCommmand Options");
            $sender->sendMessage("--help Display help");
            $sender->sendMessage("--all_height Display all areas of land with particles");
            $sender->sendMessage("--all_sides Display particles on all sides");
            return true;
        }

        $task = Main::getInstance()->getScheduler()->scheduleRepeatingTask(new ParticleTask($land, \floor($sender->y), $count, $args), 5);
        self::$runningTasks[$land[LandManager::ID]] = $task;
        $sender->sendMessage(Main::getMessage("particle-active", [$count / 4]));

        return true;
    }
}
