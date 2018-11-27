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

namespace metowa1227;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\{
	Config,
	TextFormat
};
use pocketmine\event\Listener;
use pocketmine\event\block\{
	SignChangeEvent,
	BlockBreakEvent
};
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Position;
use pocketmine\scheduler\Task;

use metowa1227\moneysystem\api\core\API;

class MoneySystemAirport extends PluginBase implements Listener
{
	public $cooldown = null;

	public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		@mkdir($this->getDataFolder());
		$this->data = new Config($this->getDataFolder() . "Airports.yml", Config::YAML);
		$this->getLogger()->info(TextFormat::GREEN . "MoneySystemAirport has started.");
	}

	public function onChangeSign(SignChangeEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$lines = $event->getLines();
		if ($lines[0] !== "airport")
			return;
		if (!$player->isOp()) {
			$player->sendMessage(TextFormat::RED . "あなたには空港を作成する権限がありません。");
			$event->setCancelled();
			return;
		}
		$to = $lines[1];
		$price = $lines[2];
		$comment = $lines[3];
		if (!ctype_digit($price)) {
			$event->setLine(0, TextFormat::RED . "[AIRPORT]");
			$event->setLine(2, TextFormat::RED . "不正な値");
			return;
		}
		$x = floor($block->x);
		$y = floor($block->y);
		$z = floor($block->z);
		$level = $block->getLevel()->getName();
		$pos = $x . " : " . $y . " : " . $z . " : " . $level;
		$data = [
			"Destination" => $to,
			"Price" => $price,
			"Comment" => $comment,
			"x" => $x,
			"y" => $y,
			"z" => $z,
			"Level" => $level
		];
		$event->setLine(0, TextFormat::GREEN . "[AIRPORT]");
		$event->setLine(1, "行先: " . $to);
		$event->setLine(2, "価格: " . $price);
		$this->data->set($pos, $data);
		$this->data->save();
		$player->sendPopup(TextFormat::GREEN . "空港の作成に成功しました。");
	}

	public function onBreakSign(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$x = floor($block->x);
		$y = floor($block->y);
		$z = floor($block->z);
		$level = $block->getLevel()->getName();
		$pos = $x . " : " . $y . " : " . $z . " : " . $level;
		if (!$this->data->exists($pos))
			return;
		if (!$player->isOp()) {
			$player->sendMessage(TextFormat::RED . "貴方には空港を破壊する権限がありません。");
			$event->setCancelled();
			return;
		}
		$this->data->remove($pos);
		$this->data->save();
		$player->sendMessage(TextFormat::GREEN . "空港を破壊しました。");
	}

	public function onTouchSign(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK)
			return;
		if (isset($this->cooldown[$name]))
			return;
		$this->cooldown[$name] = true;
		$this->getScheduler()->scheduleDelayedTask(
			new class($this, $name) extends Task {
        		public function __construct(MoneySystemAirport $class, string $name) {
        			$this->class = $class;
        			$this->name = $name;
        		}

        		public function onRun(int $tick) {
        			unset($this->class->cooldown[$this->name]);
        		}
			}, 5
		);
		$block = $event->getBlock();
		$x = floor($block->x);
		$y = floor($block->y);
		$z = floor($block->z);
		$level = $block->getLevel()->getName();
		$pos = $x . " : " . $y . " : " . $z . " : " . $level;
		if (!$this->data->exists($pos))
			return;
		$data = $this->data->get($pos);
		foreach ($this->data->getAll() as $all) {
			if ($all["Destination"] !== $data["Destination"])
				continue;
			$posAll = $all["x"] . " : " . $all["y"] . " : " . $all["z"] . " : " . $all["Level"];
			if ($pos === $posAll)
				continue;
			$level = $this->getServer()->getLevelByName($all["Level"]);
			if (!API::getInstance()->reduce($player, $all["Price"])) {
				$player->sendMessage(TextFormat::RED . "離陸に失敗しました。(不明なエラー)");
				return;
			}
			$player->teleport(new Position($all["x"], $all["y"], $all["z"], $level));
			$player->sendMessage(TextFormat::GREEN . "ご搭乗いただき、誠にありがとうございました。");
			return;
		}
		$player->sendMessage(TextFormat::RED . "目的地の空港が見つかりませんでした。");
		return;
	}
}
