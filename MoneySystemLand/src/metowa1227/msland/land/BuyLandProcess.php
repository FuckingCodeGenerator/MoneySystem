<?php

declare(strict_types=1);

namespace metowa1227\msland\land;

use pocketmine\Player;
use pocketmine\level\Position;
use metowa1227\msland\Main;
use metowa1227\msland\form\BuyForm;
use metowa1227\msland\model\BuyLandModel;

class BuyLandProcess extends BuyLandModel
{
    public function __construct(Player $player)
    {
        parent::__construct($player);
    }

    /**
     * 購入処理中のプロセスリストを取得
     *
     * @return array
     */
    public static function getProcessingList(): array
    {
        return self::$processingList;
    }

    /**
     * プレイヤーの購入処理中のプロセスを取得
     *
     * @param Player $player
     * @return self|null
     */
    public static function getProcess(Player $player): ?self
    {
        return (isset(self::$processingList[$player->getName()])) ? self::$processingList[$player->getName()] : null;
    }

    /**
     * 現在表示中の購入画面のインスタンスを取得
     *
     * @return BuyForm
     */
    public function getBuyForm(): BuyForm
    {
        return $this->buyForm;
    }

    /**
     * 購入処理中のプロセスを削除
     *
     * @param Player $player
     * @return void
     */
    public static function unsetProcess(Player $player): void
    {
        unset(self::$processingList[$player->getName()]);
    }

    /**
     * 購入地点の最初の地点を設定
     *
     * @param Position $pos
     * @return void
     */
    public function setFirstPos(Position $pos): void
    {
        $pos->x = $pos->getFloorX();
        $pos->y = $pos->getFloorY();
        $pos->z = $pos->getFloorZ();
        $this->firstPos = $pos;
    }

    /**
     * 購入地点の最後の地点を設定
     *
     * @param Position $pos
     * @return void
     */
    public function setSecondPos(Position $pos): void
    {
        $pos->x = $pos->getFloorX();
        $pos->y = $pos->getFloorY();
        $pos->z = $pos->getFloorZ();
        $this->secondPos = $pos;
    }

    /**
     * 購入地点の最初の地点を取得
     *
     * @return Position|null
     */
    public function getFirstPos(): ?Position
    {
        return $this->firstPos;
    }

    /**
     * 購入地点の最後の地点を取得
     *
     * @return Position|null
     */
    public function getSecondPos(): ?Position
    {
        return $this->secondPos;
    }

    /**
     * 土地の購入処理
     *
     * @param Player $player
     * @return boolean
     */
    public function buyLand(Player $player, Main $owner): void
    {
        $this->buyForm = new BuyForm($owner, $player);
        $this->buyForm->sendForm(BuyForm::FORM_FIRST_SELECT_MODAL, $player);
    }
}
