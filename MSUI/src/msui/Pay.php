<?php
namespace msui;

use pocketmine\utils\Config;
use metowa1227\moneysystem\api\traits\GetNameTrait;

class Pay
{
	use GetNameTrait;

	/** @var Config */
	private static $tmp;

	public function __construct(Main $owner)
	{
		self::$tmp = new Config($owner->getDataFolder() . "tmp.yml", Config::YAML);
	}

    /**
     * 寄付を削除
     *
     * @param Player | string $player
     *
     * @return bool
    */
    protected function removeDonation($player) : bool
    {
        $this->getName($player);
        self::$tmp->remove($player);
        self::$tmp->save();
        return true;
    }

    /**
     * 寄付情報取得
     *
     * @return array
    */
    protected function getDonation() : array
    {
        return self::$tmp->getAll();
    }

    /**
     * 寄付
     *
     * @param Player | string $player
     * @param string 		  $to
     * @param int 			  $amount
     *
     * @return bool
    */
    protected function addDonation($from, string $to, int $amount) : bool
    {
        $this->getName($from);
        if (!self::$tmp->exists($to)) {
            self::$tmp->set($to, ["amount" => $amount, "from" => $from]);
            self::$tmp->save();
            return true;
        }
        $donator = self::$tmp->get($to);
        $from = (!preg_match('/' . $from . '/', $donator["from"])) ? $donator["from"] . ", " . $from : $from;
        self::$tmp->set($to, ["amount" => $donator["amount"] + $amount, "from" => $from]);
        self::$tmp->save();
        return true;
    }
}