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

namespace MoneySystemJob\command;

use pocketmine\utils\TextFormat;
use pocketmine\command\{
	Command,
	CommandSender
};

use MoneySystemJob\MoneySystemJob as Main;
use MoneySystemJob\form\CreateForm;

class JobCommand extends Command
{
	public function __construct(Main $main)
	{
        parent::__construct("job", "OpenForm", "/job");
        $this->setPermission("moneysystemjob.job");
        $this->createForm = new CreateForm();
	}

	public function execute(CommandSender $sender, string $label, array $args) : bool
	{
		$this->createForm->openForm($sender);
		return true;
	}
}
