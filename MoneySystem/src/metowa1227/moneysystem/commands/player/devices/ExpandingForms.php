<?php

/*
*  __  __       _                             __    ___    ___   _______
* |  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
* | |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
* | |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
* |_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
* All this program is made by hand of metowa1227.
* I certify here that all authorities are in metowa1227.
* Expiration date of certification: unlimited
* Secondary distribution etc are prohibited.
* The update is also done by the developer.
* This plugin is a developer API plugin to make it easier to write code.
* When using this plug-in, be sure to specify it somewhere.
* Warning if violation is confirmed.
*
* Developer: metowa1227
*/

/*
    Plugin description

    - CONTENTS
        - Lightweight, fast and multifunctional economic system.

    - AUTHOR
        - metowa1227 (MoneySystemAPI)

    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Pro 64bit
        - Intel(R) Core 2 Duo(TM) E8400 @ 3.00GHz
        - 8192MB DDR2 SDRAM PC2-5300(667MHz) , PC2-6400(800MHz)
        - Altay 3.0.6+dev for Minecraft: PE v1.5.0 (protocol version 274)
        - PHP 7.2.1 64bit supported version
*/

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
