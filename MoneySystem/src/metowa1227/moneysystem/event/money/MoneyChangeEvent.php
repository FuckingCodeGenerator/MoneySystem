<?php
namespace metowa1227\moneysystem\event\money;

use pocketmine\event\Cancellable;
use metowa1227\moneysystem\event\player\PlayerEvent;

class MoneyChangeEvent extends PlayerEvent implements Cancellable
{
    /** @var int */
    const TYPE_INCREASE = 1;
    const TYPE_REDUCE = 2;
    const TYPE_SET = 3;

    /** @var string */
    private $reason, $by;
    protected $player;
    /** @var int */
    private $money, $type, $before;

    public function __construct(string $player, int $money, string $reason, string $by, int $type, int $before)
    {
        $this->player = $player;
        $this->money = $money;
        $this->reason = $reason;
        $this->by = $by;
        $this->type = $type;
        $this->before = $before;
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

    /**
     * 変更前の所持金を取得する
     *
     * @return int
    */
    public function getBefore() : int
    {
        return $this->before;
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
