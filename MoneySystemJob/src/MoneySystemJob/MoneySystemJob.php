<?php

/*
* __  __       _                             __    ___    ___   _______
*|  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
*| |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
*| |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
*|_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
*All this program is made by hand of metowa 1227 . 
*I certify here that all authorities are in metowa 1227 . 
*Expiration date of certification: unlimited
*Secondary distribution etc are prohibited . 
*The update is also done by the developer . 
*This plugin is a developer API plugin to make it easier to write code . 
*When using this plug-in, be sure to specify it somewhere . 
*Warning if violation is confirmed . 
*
*Developer: metowa 1227
*Development Team: metowa 1227 Plugin Development Team (Members: metowa 1227 only)
*/

/*
    PluginIntrodtion
    - CONTENTS
        - MoneySystem job
    - AUTHOR
        - metowa1227 (MoneySystemJob)
    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Pro 64bit
        - Intel(R) Core 2 Duo(TM) E8400 @ 3 . 00GHz
        - 8192MB DDR2 SDRAM PC2-5300(667MHz) , PC2-6400(800MHz)
        - 1 . 7dev-1001「[REDACTED]」Minecraft PE v1 . 4 . 0用実装APIバージョン3 . 0 . 0-ALPHA12(プロトコルバージョン261)
        - PHP 7 . 2 . 1 64bit supported version
        - MoneySystemAPI (SYSTEM) version 12 . 1 package version 12 . 00 API version 11 . 1 GREEN PAPAYA GT3 Edition (Releaced date: 2018/06/09)
*/

namespace MoneySystemJob;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\Player;
use metowa1227\MoneySystemAPI\MoneySystemAPI;
use pocketmine\utils\TextFormat;

use metowa1227\moneysystem\api\core\API;

class MoneySystemJob extends PluginBase implements Listener
{
	
	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	    if (!file_exists($this->getDataFolder())) {
	    	@mkdir($this->getDataFolder(), 0755, true); 
	    }
		if (!is_file($this->getDataFolder() . "jobs.yml")) {
			$this->jobs = new Config($this->getDataFolder() . "jobs.yml", Config::YAML, yaml_parse($this->readResource("jobs.yml")));
		} else {
		    $this->jobs = new Config($this->getDataFolder()  .  "jobs.yml", Config::YAML, []);
		}
	    $this->player = new Config($this->getDataFolder()  .  "playerjobs.yml", Config::YAML, []);
	    $this->Money = API::getInstance();
	    if (!$this->Money) {
	    	$this->getLogger()->error("MoneySystemAPIが導入されていません。");
	    	$this->getServer()->getPluginManager()->disablePlugin($this);
	    	return true;
	    }
	    $this->getLogger()->notice("MoneySystemJobを読み込みました。　二次配布は禁止です。　製作者: metowa1227");
	}

	private function readResource($res)
	{
		$path = $this->getFile() . "resources/" . $res;
		$resource = $this->getResource($res);
		$content = stream_get_contents($resource);
		@fclose($content);
		return $content;
	}

	public function onBlockBreak(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
        $name = $player->getName();
		$job = $this->jobs->get($this->player->get($player->getName()));
		if ($job !== false) {
			if (isset($job[$block->getID() . ":" . $block->getDamage() . ":break"])) {
				$money = $job[$block->getID() . ":" . $block->getDamage() . ":break"];
				if ($money > 0) {
					$this->Money->increase($name, $money);
				} else {
					$this->Money->reduce($name, $money);
				}
			}
		}
	}

	public function getJobs()
	{
		return $this->jobs->getAll();
	}

	public function getPlayers()
	{
		return $this->player->getAll();
	}

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        if (!$sender instanceof Player) {
        	$sender->sendMessage("このコマンドはゲーム内で使用してください");
        	return true;
        }
        $name = $sender->getName();
        $player = $sender->getPlayer();
        if ($command->getName() == "job" and !isset($args[0])) {
        	return false;
        }
		switch (array_shift($args)) {
            case "join":
				if ($this->player->exists($sender->getName())) {
					$sender->sendMessage(">>貴方はすでに職に就いています。");
					return true;
				} else {
    	            if (!isset($args[0])) {
          				$sender->sendMessage("§e>>仕事名を入力してください。");
				        return true;
	      			}
					$job = array_shift($args);
					if ($this->jobs->exists($job)) {
						$this->player->set($sender->getName(), $job);
						$this->player->save();
						$sender->sendMessage(">>[INFO]>>" . $job . "に就きました。");
						return true;
					} else {
						$sender->sendMessage(">>" . $job . "という仕事は存在しません。");
						return true;
					}
				}
				break;
		
				case "me":
					if ($this->player->exists($sender->getName())) {
						$sender->sendMessage("貴方の仕事はこちらです。 : " . $this->player->get($sender->getName()));
						return true;
					} else {
						$sender->sendMessage("§c貴方は仕事に就いていません。");
						return true;
					}
				break;

				case "out":
					if ($this->player->exists($sender->getName())) {
						$job = $this->player->get($sender->getName());
						$this->player->remove($sender->getName());
						$this->player->save();
						$sender->sendMessage("§a>>貴方は" . $job . "を辞職しました。");
						return true;
					} else {
						$sender->sendMessage("§c貴方は仕事に就いていません。");
						return true;
					}
				break;

				case "list":
					if (!isset($args[0])) {
						$sender->sendMessage("§a[INFO]>>仕事一覧表<< 現在のページ page 1 / 3");
						$sender->sendMessage(">>仕事: 木こり");
						$sender->sendMessage("お金獲得ブロック:");
						$sender->sendMessage("17:0 オークの原木 17:1 ダークオークの原木");
						$sender->sendMessage("17:2 白樺の原木 17:3 ジャングルの原木");
						$sender->sendMessage("18:0 オークの葉 18:1 ダークオークの葉");
						$sender->sendMessage("18:2 白樺の葉 18:3 ジャングルの葉");
						$sender->sendMessage("161:0 アカシアの葉 162:0 アカシアの原木");
						return true;
					} else {
						switch($args[0]) {
							case "2":
								$sender->sendMessage("§a[INFO]>>仕事一覧表<< 現在のページ page 2 / 3");
								$sender->sendMessage(">>仕事: 石堀り");
								$sender->sendMessage("お金獲得ブロック:");
								$sender->sendMessage("1:0 石 4:0 丸石");
								return true;
							break;

							case "3":
								$sender->sendMessage("§a[INFO]>>仕事一覧表<< 現在のページ page 3 / 3");
								$sender->sendMessage(">>仕事: 鉱夫");
								$sender->sendMessage("お金獲得ブロック:");
								$sender->sendMessage("14:0 金の鉱石 15:0 鉄の鉱石");
								$sender->sendMessage("16:0 石炭の鉱石 21:0 ラピスラズリの鉱石");
								$sender->sendMessage("73:0 レッドストーンの鉱石 129:0 エメラルドの鉱石 56:0 ダイヤモンドの鉱石");
								return true;
							break;

							default:
								$sender->sendMessage("/list 1/2/3しか存在しません。");
								return true;
						}
					}
				}
			}
		}