<?php
declare(strict_types = 1);

namespace metowa1227\command;

use pocketmine\Player;

interface Listener
{
	/**
	 * @param args Array
	 * @param sender Player
	**/
	public static function see(array $args, $sender) : void;

	/**
	 * @param args   Array
	 * @param sender Player
	 * @param amount Int
	**/
	public static function pay(array $args, $sender) : void;

	/**
	 * @param player  Player | ConsoleCommandSender
	 * @param command Int CommandType
	 * @return bool
	**/
	public static function isConsole($player, int $command) : bool;
}
