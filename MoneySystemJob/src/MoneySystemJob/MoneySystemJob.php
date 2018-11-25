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

namespace MoneySystemJob;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\{
	Config,
	TextFormat
};

use MoneySystemJob\event\{
	BlockBreak,
	BlockPlace
};
use MoneySystemJob\command\JobCommand;
use MoneySystemJob\task\SaveTask;
use MoneySystemJob\api\JobAPI;

class MoneySystemJob extends PluginBase
{
	public $jobList, $playersJobFile, $config;
	public static $playersJob;

	public function onEnable()
	{
		$dataPath = $this->getDataFolder();
		@mkdir($dataPath);
		$this->initConfigs($dataPath);
		(new JobAPI($this));
		(\tokyo\pmmp\libform\FormApi::register($this));
		$this->getServer()->getCommandMap()->register("job", new JobCommand($this));
		$this->getServer()->getPluginManager()->registerEvents(new BlockBreak(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new BlockPlace(), $this);
		$this->getScheduler()->scheduleRepeatingTask(new SaveTask($this), 20 * 60 * 5);
		$this->getLogger()->info(TextFormat::GREEN . "MoneySystemJob has started.");
	}

	private function initConfigs($path) : void
	{
		$this->saveResource("JobList.yml");
		$this->saveResource("Config.yml");
		$this->jobList = (new Config($path . "JobList.yml", Config::YAML))->getAll();
		$this->playersJobFile = new Config($path . "PlayersJobList.yml", Config::YAML);
		$this->config = new Config($path . "Config.yml", Config::YAML);
		self::$playersJob = $this->playersJobFile->getAll();
	}

	public function onDisable()
	{
		$this->save();
	}

	public function save() : void
	{
		if (empty(self::$playersJob))
			return;
		$this->playersJobFile->setAll(self::$playersJob);
		$this->playersJobFile->save(true);
	}
}
