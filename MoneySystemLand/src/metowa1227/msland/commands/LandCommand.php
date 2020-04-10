<?php
namespace metowa1227\msland\commands;

use metowa1227\msland\form\MainMenuForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use metowa1227\msland\Main;

class LandCommand extends Command
{
    private const CMD_COMMAND = "land";
    private const CMD_DESCRIPTION = "MSLandのUIを表示";
    private const CMD_USAGE = "/land";

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
        
        MainMenuForm::createUi($sender);
        return true;
    }
}
