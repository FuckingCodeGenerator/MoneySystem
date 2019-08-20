<?php
namespace metowa1227\moneysystem\commands\main;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use metowa1227\moneysystem\api\core\API;
use metowa1227\moneysystem\core\System;

class SystemCommands extends Command
{
    public function __construct(System $system)
    {
        parent::__construct("moneysystem", "MoneySystem information", "/moneysystem");
        $this->setPermission("moneysystem.system.info");
        $this->main = $system;
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        for ($i = 0; $i <= 4; $i++) {
            $sender->sendMessage(API::getInstance()->getMessage("command.system-guide-" . $i));
        }
        return true;
    }
}
