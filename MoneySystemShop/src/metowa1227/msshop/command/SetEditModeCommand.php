<?php
namespace metowa1227\msshop\command;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use metowa1227\msshop\Main;

class SetEditModeCommand extends Command
{
    /** @var Main */
    private $owner;

    public function __construct(Main $owner)
    {
        parent::__construct("mshedit", "MoneySystemShop の編集モードを切り替えます", "/mshedit <on/off>");
        $this->setPermission("msshop.command.mshedit");
        $this->owner = $owner;
    }

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::YELLOW . "Please run this command in-game");
            return false;
        }

        if (!$this->testPermission($sender)) return false;
        if (!isset($args[0])) {
            $sender->sendMessage($this->getUsage());
            return false;
        }

        switch ($args[0]) {
            case 'on':
                $this->owner->enableEditMode($sender->getName());
                $sender->sendMessage("Enabled edit mode");
                return true;
            case 'off':
                $this->owner->disableEditMode($sender->getName());
                $sender->sendMessage("Disabled edit mode");
                return true;
            default:
                $sender->sendMessage($this->getUsage());
                return false;
        }
    }
}
