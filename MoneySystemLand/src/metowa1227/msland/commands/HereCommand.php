<?php
namespace metowa1227\msland\commands;

use metowa1227\msland\land\LandManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use metowa1227\msland\Main;
use pocketmine\level\Position;

class HereCommand extends Command
{
    private const CMD_COMMAND = "here";
    private const CMD_DESCRIPTION = "現在の地点の土地情報を取得";
    private const CMD_USAGE = "/here";

    /** @var Main */
    private $owner;

    public function __construct(Main $owner)
    {
        parent::__construct(self::CMD_COMMAND, self::CMD_DESCRIPTION, self::CMD_USAGE);
        $this->setPermission("msland.command.land");

        $this->owner = $owner;
    }

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if (!Main::isPlayer($sender)) {
            $sender->sendMessage($this->owner->getMessage("in-game-only"));
            return false;
        }
        
        // getPositionを使うと、整数と浮動小数点数での判別になるため、不正確
        $land = $this->owner->getLandManager()->getLandByPosition($sender->getPosition());
        
        if ($land === null) {
            $sender->sendMessage($this->owner->getMessage("here-result-no-owner"));
        } else {
            $sender->sendMessage($this->owner->getMessage("here-result", [$land[LandManager::Owner], $land[LandManager::ID]]));
        }
        return true;
    }
}
