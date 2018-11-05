<?php

/*
* __  __       _                             __    ___    ___   _______
*|  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
*| |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
*| |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
*|_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
*All this program is made by hand of metowa 1227.
*I certify here that all authorities are in metowa 1227.
*Expiration date of certification: unlimited
*Secondary distribution etc are prohibited.
*The update is also done by the developer.
*This plugin is a developer API plugin to make it easier to write code.
*When using this plug-in, be sure to specify it somewhere.
*Warning if violation is confirmed.
*
*Developer: metowa 1227
*Development Team: metowa 1227 Plugin Development Team (Members: metowa 1227 only)
*/

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
