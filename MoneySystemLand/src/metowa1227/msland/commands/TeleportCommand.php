<?php
namespace metowa1227\msland\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use metowa1227\msland\Main;
use metowa1227\msland\teleport\LandTeleporter;
use pocketmine\command\utils\InvalidCommandSyntaxException;

class TeleportCommand extends Command
{
    private const CMD_COMMAND = "ltp";
    private const CMD_DESCRIPTION = "指定した土地にテレポートする";
    private const CMD_USAGE = "/ltp <LandID>";

    /** @var Main */
    private $owner;

    public function __construct(Main $owner)
    {
        parent::__construct(self::CMD_COMMAND, self::CMD_DESCRIPTION, self::CMD_USAGE);
        $this->setPermission("msland.command.ltp");

        $this->owner = $owner;
    }

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if (!Main::isPlayer($sender)) {
            $sender->sendMessage($this->owner->getMessage("in-game-only"));
            return false;
        }
        if (!isset($args[0])) {
            throw new InvalidCommandSyntaxException();
        }
        if (!\ctype_digit($args[0])) {
            $sender->sendMessage(Main::getMessage("enter-number"));
            return false;
        }
        
        $landId = intval($args[0]);
        if (($land = $this->owner->getLandManager()->getLandById($landId)) === null) {
            $sender->sendMessage(Main::getMessage("land-not-found"));
            return false;
        }

        LandTeleporter::teleportToLand($sender, $land);
        return true;
    }
}
