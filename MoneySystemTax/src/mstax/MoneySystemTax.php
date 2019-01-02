<?php
namespace mstax;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use mstax\task\TaxTask;

class MoneySystemTax extends PluginBase
{
	public function onEnable()
	{
		$this->initConfig();
		$this->startTask();
		$this->getLogger()->info("TaxTask has started.");
	}

	/**
	 * Initalize config file
	 */
	private function initConfig() : void
	{
		if (!is_dir($this->getDataFolder())) {
			mkdir($this->getDataFolder());
		}

		$this->config = (new Config($this->getDataFolder() . "Config.yml", Config::YAML, ["Collection-interval" => 5, "Tax" => 1000]))->getAll();
	}

	/**
	 * Start tax task
	 */
	private function startTask() : void
	{
		$this->getScheduler()->scheduleRepeatingTask(new TaxTask($this->config["Tax"]), $this->config["Collection-interval"] * 20 * 60);
	}
}
