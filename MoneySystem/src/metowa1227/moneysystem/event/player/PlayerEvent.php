<?php
namespace metowa1227\moneysystem\event\player;

use pocketmine\event\Event;
use pocketmine\Server;

abstract class PlayerEvent extends Event
{
	/* @var string */
	protected $player;

	public function getPlayer()
	{
        if (empty($player = Server::getInstance()->getPlayer($this->player))) {
            if (empty($player2 = Server::getInstance()->getOfflinePlayer($this->player))) {
                return null;
            }
            return $player2;
        }
        return $player;
	}

    public function getUser() : string
    {
        return $this->player;
    }
}
