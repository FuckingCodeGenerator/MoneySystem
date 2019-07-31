<?php
namespace msui\command;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use msui\task\DebugTask;
use msui\Main;

class DebugCommand extends Command
{
	/** @var Main */
	private $owner;
	/** @var array */
	private $tasks = [];

	public function __construct(Main $owner)
	{
        parent::__construct("msdebug", "MoneySystemのデバッグコマンド", "/msdebug");
        $this->setPermission("msui.command.debug");
        $this->owner = $owner;
	}

	public function execute(CommandSender $sender, string $label, array $args) : bool
	{
		if (!$sender instanceof Player) {
			$sender->sendMessage($this->owner->getMessage("only-in-game"));
			return true;
		}
		$name = $sender->getName();

		if (!isset($this->tasks[$name])) {
			$this->tasks[$name] = $this->owner->getScheduler()->scheduleRepeatingTask(new DebugTask($sender), 1);
		} else {
			$this->owner->getScheduler()->cancelTask($this->tasks[$name]->getTaskId());
		}
		return true;
	}
}
