<?php
namespace metowa1227\command;

use pocketmine\command\{
	Command,
	CommandSender
};
use pocketmine\Player;
use pocketmine\utils\TextFormat;

use metowa1227\moneysystem\api\core\API;

class MainCommand extends Command
{
	public function __construct()
	{
        parent::__construct("money", "MoneySystem master command", "/money <option...(help)>");
        $this->setPermission("moneysystem.command.master");
	}

	public function execute(CommandSender $sender, string $label, array $args) : bool
	{
		if (!isset($args[0])) {
			$sender->sendMessage($this->getUsage());
			return true;
		}

		$api = API::getInstance();
		switch ($args[0]) {
			case "me":
				if (ExecuteCommands::isConsole($sender, Commands::MS_COMMAND_ME))
					return true;
				$sender->sendMessage("貴方の所持金: " . $api->getUnit() . $api->get($sender));
				return true;
			case "see":
				ExecuteCommands::see($args, $sender);
				return true;
			case "pay":
				ExecuteCommands::pay($args, $sender);
				return true;
			case "help":
				ExecuteCommands::help($sender);
				return true;
		}

		if (!$sender->isOp()) {
			$sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
			return true;
		}

		switch ($args[0]) {
			case "increase":
				ExecuteCommands::increase($args, $sender);
				return true;
			case "reduce":
				ExecuteCommands::reduce($args, $sender);
				return true;
			case "set":
				ExecuteCommands::set($args, $sender);
				return true;
		}

		return true;
	}
}
