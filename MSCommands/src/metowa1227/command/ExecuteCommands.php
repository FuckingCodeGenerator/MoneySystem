<?php
namespace metowa1227\command;

use pocketmine\{
	Player,
	Server
};
use pocketmine\utils\TextFormat;

use metowa1227\moneysystem\api\core\API;
use metowa1227\moneysystem\event\money\{
	MoneyReduceEvent,
	MoneyIncreaseEvent,
	MoneySetEvent
};

class ExecuteCommands implements Listener
{
	protected static $cancelPay_reduce, $cancelPay_increase, $cancelRed_reduce, $cancelInc_increase, $cancelSet_set = null;

	public static function isConsole($player, int $command) : bool
	{
		if ($player instanceof Player)
			return false;
		switch ($command) {
			case Commands::MS_COMMAND_ME:
				Server::getInstance()->getLogger()->info("このコマンドはコンソールからは実行できません。");
				return true;
		}
	}

	public static function see(array $args, $sender) : void
	{
		if (!isset($args[1])) {
			$sender->sendMessage(TextFormat::YELLOW . "所持金を確認するプレイヤー名を入力してください。");
			return;
		}
		$api = API::getInstance();
		if (!$api->exists($args[1])) {
			$sender->sendMessage(TextFormat::YELLOW . "プレイヤーが見つかりませんでした。");
			return;
		}
		$sender->sendMessage($args[1] . "の所持金: " . $api->getUnit() . $api->get($args[1]));
		return;
	}

	public static function pay(array $args, $sender) : void
	{
		$api = API::getInstance();
		if (!isset($args[1]) || !isset($args[2])) {
			$sender->sendMessage(TextFormat::YELLOW . "プレイヤー名もしくは金額が入力されていません。");
			return;
		}
		if (!$api->exists($args[1])) {
			$sender->sendMessage(TextFormat::YELLOW . "プレイヤーが見つかりませんでした。");
			return;
		}
		$args[2] = intval($args[2]);
		if ($api->get($sender) < $args[2]) {
			$sender->sendMessage(TextFormat::YELLOW . "所持金が不足しています。");
			return;
		}
		if ($args[2] <= 0) {
			$sender->sendMessage(TextFormat::YELLOW . "送金額は0以上でなければいけません。");
			return;
		}
		$result1 = $api->reduce($sender, $args[2]);
		$result2 = $api->increase($args[1], $args[2]);
		if (!($result1 && $result2)) {
			self::$cancelPay_reduce[$sender->getName()] = true;
			self::$cancelPay_increase[$sender->getName()] = true;
			return;
		}
		$sender->sendMessage("送金に成功しました。 [ " . $api->getUnit() . $args[2] . " ]");
		return;
	}

	public static function help($sender) : void
	{
		$sender->sendMessage("/money <option>");
		$sender->sendMessage("Options:");
		$sender->sendMessage("- me: 所持金を表示");
		$sender->sendMessage("- pay <プレイヤー> <金額>: プレイヤーに送金する");
		$sender->sendMessage("- see <プレイヤー>: プレイヤーの所持金を表示する");

		if (!$sender->isOp())
			return;

		$sender->sendMessage("- increase <プレイヤー> <金額>: プレイヤーの所持金を増やす");
		$sender->sendMessage("- reduce <プレイヤー> <金額>: プレイヤーの所持金を減らす");
		$sender->sendMessage("- set <プレイヤー> <金額>: プレイヤーの所持金を設定する");
		return;
	}

	public static function increase(array $args, $sender) : void
	{
		$api = API::getInstance();
		if (!isset($args[1]) || !isset($args[2])) {
			$sender->sendMessage(TextFormat::YELLOW . "プレイヤー名もしくは金額が入力されていません。");
			return;
		}
		if (!$api->exists($args[1])) {
			$sender->sendMessage(TextFormat::YELLOW . "プレイヤーが見つかりませんでした。");
			return;
		}
		$args[2] = intval($args[2]);
		if ($args[2] <= 0) {
			$sender->sendMessage(TextFormat::YELLOW . "金額は0以上でなければいけません。");
			return;
		}
		$result = $api->increase($args[1], $args[2]);
		if (!$result) {
			self::$cancelInc_increase[$sender->getName()] = true;
			return;
		}
		$sender->sendMessage("操作に成功しました。");
		return;
	}

	public static function reduce(array $args, $sender) : void
	{
		$api = API::getInstance();
		if (!isset($args[1]) || !isset($args[2])) {
			$sender->sendMessage(TextFormat::YELLOW . "プレイヤー名もしくは金額が入力されていません。");
			return;
		}
		if (!$api->exists($args[1])) {
			$sender->sendMessage(TextFormat::YELLOW . "プレイヤーが見つかりませんでした。");
			return;
		}
		$args[2] = intval($args[2]);
		if ($args[2] <= 0) {
			$sender->sendMessage(TextFormat::YELLOW . "金額は0以上でなければいけません。");
			return;
		}
		$result = $api->reduce($args[1], $args[2]);
		if (!$result) {
			self::$cancelRed_reduce[$sender->getName()] = true;
			return;
		}
		$sender->sendMessage("操作に成功しました。");
		return;
	}

	public static function set(array $args, $sender) : void
	{
		$api = API::getInstance();
		if (!isset($args[1]) || !isset($args[2])) {
			$sender->sendMessage(TextFormat::YELLOW . "プレイヤー名もしくは金額が入力されていません。");
			return;
		}
		if (!$api->exists($args[1])) {
			$sender->sendMessage(TextFormat::YELLOW . "プレイヤーが見つかりませんでした。");
			return;
		}
		$args[2] = intval($args[2]);
		if ($args[2] <= 0) {
			$sender->sendMessage(TextFormat::YELLOW . "金額は0以上でなければいけません。");
			return;
		}
		$result = $api->set($args[1], $args[2]);
		if (!$result) {
			self::$cancelSet_set[$sender->getName()] = true;
			return;
		}
		$sender->sendMessage("操作に成功しました。");
		return;
	}

	public function onReduce(MoneyReduceEvent $event)
	{
		if (isset(self::$cancelPay_reduce[$event->getUser()])) {
			$event->setCancelled();
			unset(self::$cancelPay_reduce[$event->getUser()]);
		}
		if (isset(self::$cancelRed_reduce[$event->getUser()])) {
			$event->setCancelled();
			unset(self::$cancelRed_reduce[$event->getUser()]);
		}
	}

	public function onIncrease(MoneyIncreaseEvent $event)
	{
		if (isset(self::$cancelPay_increase[$event->getUser()])) {
			$event->setCancelled();
			unset(self::$cancelPay_increase[$event->getUser()]);
		}
		if (isset(self::$cancelInc_increase[$event->getUser()])) {
			$event->setCancelled();
			unset(self::$cancelInc_increase[$event->getUser()]);
		}
	}

	public function onSet(MoneySetEvent $event)
	{
		if (isset(self::$cancelSet_set[$event->getUser()])) {
			$event->setCancelled();
			unset(self::$cancelSet_set[$event->getUser()]);
		}
	}
}
