<?php
namespace metowa1227\moneysystem\commands\player\devices;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

use metowa1227\moneysystem\api\core\API;
use metowa1227\moneysystem\core\System;
use metowa1227\moneysystem\form\CreateForm;

class ExpandingForms extends Command
{
    public function __construct(System $system)
    {
        parent::__construct("msys", "Expanding forms", "/msys");
        $this->setPermission("moneysystem.player.form");
        $this->main = $system;
        $this->path = $system->getDataFolder();
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        $main = $this->main;
        $api = API::getInstance();
        if (!$sender instanceof Player) {
            $main->getLogger()->notice($api->getMessage("command.player-only"));
            return true;
        }
        $form = new CreateForm($sender, $this->path);
        $form->new();
        return true;
    }
}
