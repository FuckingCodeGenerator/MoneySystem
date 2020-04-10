<?php
namespace metowa1227\msland\model;

use pocketmine\Player;
use pocketmine\level\Position;
use metowa1227\msland\form\BuyForm;

class BuyLandModel
{
    /**
     * 選択座標
     *
     * @var Position
     */
    protected $firstPos, $secondPos;
    /**
     * 現在表示中の購入画面のインスタンス
     *
     * @var BuyForm
     */
    protected $buyForm;
    /**
     * 土地保護処理中のプレイヤーリスト
     *
     * @var array [PlayerName => BuyLandProcess]
     */
    protected static $processingList = [];

    public function __construct(Player $player)
    {
        self::$processingList[$player->getName()] = $this;
    }
}
