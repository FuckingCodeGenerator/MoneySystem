<?php
namespace metowa1227\moneysystem\event\money;

use pocketmine\event\Cancellable;
use pocketmine\Server;
use pocketmine\Player;
use metowa1227\moneysystem\event\player\PlayerEvent;
use metowa1227\moneysystem\api\core\API;

class MoneySetEvent extends PlayerEvent implements Cancellable
{
    public function __construct(API $api, string $player, int $money, string $reason, string $by)
    {
        $this->api = $api;
        $this->player = $player;
        $this->money = $money;
        $this->reason = $reason;
        $this->by = $by;
    }

    /**
     * APIを取得する
     *
     * @return API
    */
    public function getAPI() : API
    {
        return $this->api;
    }

    /**
     * イベント名を取得する
     *
     * @return string
    */
    public function getName() : string
    {
        return "MoneySetEvent";
    }

    /**
     * 変更理由を取得する
     *
     * @return string
    */
    public function getReason() : string
    {
        return $this->reason;
    }

    /**
     * 実行元を取得する
     *
     * @return string
    */
    public function getExecutor() : string
    {
        return $this->by;
    }

    /**
     * 変更額を取得する
     *
     * @return int
    */
    public function getAmount() : int
    {
        return $this->money;
    }
}
