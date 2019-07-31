<?php
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
