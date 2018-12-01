<?php
namespace metowa1227\moneysystem\event\money;

use pocketmine\event\Cancellable;
use pocketmine\Server;
use pocketmine\Player;
use metowa1227\moneysystem\event\player\PlayerEvent;
use metowa1227\moneysystem\api\core\API;

class MoneyChangeEvent extends PlayerEvent implements Cancellable
{
    public function __construct(API $api, string $player, int $money, string $reason, string $by, int $type)
    {
        $this->api = $api;
        $this->player = $player;
        $this->money = $money;
        $this->reason = $reason;
        $this->by = $by;
        $this->type = $type;
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
        return "MoneyChangeEvent";
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

    /*
     * [$type変数の説明]
     *
     * 1 : MoneyIncreaseEvent | お金が増えた時に発生するベント
     * 2 : MoneyReduceEvent   | お金が減ったときに発生するイベント
     * 3 : MoneySetEvent      | お金を設定したときに発生するイベント
     *
    */
    public function getType() : int
    {
        return $this->type;
    }
}
