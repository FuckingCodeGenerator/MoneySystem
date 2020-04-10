<?php
namespace metowa1227\msland\form;

use pocketmine\Player;
use metowa1227\msland\land\BuyLandProcess;
use metowa1227\msland\Main;
use metowa1227\msland\jojoe77777\FormAPI\ModalForm;
use metowa1227\msland\land\LandManager;
use metowa1227\moneysystem\api\core\API;

class BuyForm
{
    /**
     * フォームの種類
     */
    // 最終地点を設定後に自動で表示される土地を購入するかどうかのModalForm
    public const FORM_FIRST_SELECT_MODAL = 1;
    // 土地購入決定の確認のModalForm
    public const FORM_BUY_CONFIRM = 2;

    /** @var Main */
    private $owner;
    /** @var API */
    private $api;
    /** @var LandManager */
    private $landManager;
    /** @var BuyLandProcess */
    private $process;
    /** @var int */
    private $price;
    /** @var Player */
    private $player;

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getBuyLandProcess(): BuyLandProcess
    {
        return $this->process;
    }

    public function getLandManager(): LandManager
    {
        return $this->landManager;
    }

    public function getOwner(): Main
    {
        return $this->owner;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function __construct(Main $owner, Player $player)
    {
        $this->owner = $owner;
        $this->player = $player;
        $this->api = API::getInstance();
        $this->landManager = $owner->getLandManager();
        $this->process = BuyLandProcess::getProcessingList()[$player->getName()];
        $this->price = $this->landManager->getLandPrice($this->process);
    }

    /**
     * フォームを送信
     *
     * @param integer $formType
     * @param Player $player
     * @return void
     */
    public function sendForm(int $formType, Player $player): void
    {
        switch ($formType) {
            // 最終地点設定後、表示される購入するかどうかの画面
            case self::FORM_FIRST_SELECT_MODAL:
                $form = new ModalForm(FirstSelectModalForm::getFunc($this));
                $form->setTitle($this->owner->getMessage("buyform-select-modal-title"));
                $form->setContent($this->owner->getMessage("buyform-select-modal-content", [$this->api->getUnit(), $this->price]));
                $form->setButton1($this->owner->getMessage("buyform-select-modal-button1"));
                $form->setButton2($this->owner->getMessage("buyform-select-modal-button2"));
                $form->sendToPlayer($player);
            break;

            // 土地購入決定の確認のModalForm
            case self::FORM_BUY_CONFIRM:
                $form = new ModalForm(BuyConfirmForm::getFunc($this));
                $form->setTitle($this->owner->getMessage("buyform-buy-confirm-title"));
                $form->setContent($this->owner->getMessage("buyform-buy-confirm-content", [$this->api->getUnit(), $this->price]));
                $form->setButton1($this->owner->getMessage("buyform-buy-confirm-button1"));
                $form->setButton2($this->owner->getMessage("buyform-buy-confirm-button2"));
                $form->sendToPlayer($player);
            break;
        }
    }
}
