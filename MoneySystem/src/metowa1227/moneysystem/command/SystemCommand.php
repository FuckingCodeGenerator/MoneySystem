<?php
namespace metowa1227\moneysystem\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use metowa1227\moneysystem\api\core\API;

class SystemCommand extends Command
{
    private const CMD = "moneysystem";
    private const DESCRIPTION = "MoneySystem information";
    private const USAGE = "/moneysystem";

    public function __construct()
    {
        parent::__construct(self::CMD, self::DESCRIPTION, self::USAGE);
        $this->setPermission("moneysystem.command.moneysystem");
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        for ($i = 0; $i <= 4; $i++) {
            $sender->sendMessage(API::getInstance()->getMessage("command.system-guide-" . $i));
        }
        return true;
    }
}
