<?php
namespace MoneySystemShop\command;

use pocketmine\command\{
	Command,
	CommandSender
};
use pocketmine\Player;

class IDCommand extends Command
{
    public function __construct()
    {
        parent::__construct("id", "ID Checker", "/id");
        $this->setPermission("msshop.command.id");
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("このコマンドはコンソールからは実行できません。");
            return true;
        }
        $item = $sender->getInventory()->getItemInHand();
        $id = $item->getID();
        $meta = $item->getDamage();
        $sender->sendMessage("Item ID checker | " . $id . ":" . $meta . "");
        return true;
    }
}
