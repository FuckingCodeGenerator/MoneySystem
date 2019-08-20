<?php
namespace metowa1227\moneysystem\api\processor;

use metowa1227\moneysystem\api\listener\Types;
use metowa1227\moneysystem\api\processor\{
	IncreaseProcess,
	ReduceProcess,
	SetProcess
};
use metowa1227\moneysystem\api\core\API;

class Processor implements Types
{
	use GetName, Check;
	/**
     * @param Player | string | array  $player
     * @param int                      $money
     * @param string                   $reason
     * @param string                   $by [caller]
     * @param int                      $type
     * @param SQLiteDataManager        $db
     *
     * @return bool
     */
	final protected function process($player, $money, $reason, $by, $type, $db) : bool
	{
		$this->getName($player);
		switch ($type) {
			case self::TYPE_SET:
				return SetProcess::run($player, $this->check($money), $reason, $by, $type, $db);
			case self::TYPE_INCREASE:
				return IncreaseProcess::run($player, $this->check($money), $reason, $by, $type, $db);
			case self::TYPE_REDUCE:
				$money = $this->check($money);
	            $money = API::getInstance()->get($player) - $money;
				return ReduceProcess::run($player, $this->check($money), $reason, $by, $type, $db);
		} 
	}
}	
