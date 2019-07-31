<?php
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
