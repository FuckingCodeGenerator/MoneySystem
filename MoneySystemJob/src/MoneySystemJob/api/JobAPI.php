<?php
namespace MoneySystemJob\api;

use pocketmine\Player;
use pocketmine\utils\Config;

use MoneySystemJob\MoneySystemJob as Main;

class JobAPI
{
	public static $instance;

	public function __construct(Main $main)
	{
		$this->jobList = $main->jobList;
		$this->config = $main->config;
		self::$instance = $this;
	}

	public static function getInstance() : self
	{
		return self::$instance;
	}

	public function getConfig() : Config
	{
		return $this->config;
	}

	public function getJob($player)
	{
		$this->getPlayer($player);
		return isset(Main::$playersJob[$player]) ? Main::$playersJob[$player] : null;
	}

	private function getPlayer(&$player) : void
	{
		if ($player instanceof Player)
			$player = $player->getName();
	}

	public function getJobList() : array
	{
		return $this->jobList;
	}

	public function getAllJobs($returnKey = false) : array
	{
		$return = [];
		foreach ($this->jobList as $key) {
			foreach (array_keys($key) as $key) {
				array_push($return, $key);
			}
		}
		return $returnKey ? $return : $this->jobList;
	}

	public function joinJob(Player $player, int $data) : bool
	{
		$i = 0;
		$job = null;
		foreach ($this->getAllJobs(true) as $jobs) {
			if ($i === $data) {
				$job = $jobs;
				break;
			}
			$i++;
		}
		Main::$playersJob[$player->getName()] = $job;
		return true;
	}

	public function resignJob(Player $player) : bool
	{
		unset(Main::$playersJob[$player->getName()]);
		return true;
	}
}
