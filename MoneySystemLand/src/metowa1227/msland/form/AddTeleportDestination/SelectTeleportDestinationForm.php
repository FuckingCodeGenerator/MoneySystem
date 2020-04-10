<?php
namespace metowa1227\msland\form\AddTeleportDestination;

use metowa1227\msland\form\TeleportForm;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use metowa1227\msland\Main;
use metowa1227\msland\jojoe77777\FormAPI\SimpleForm;
use metowa1227\msland\land\LandManager;

class SelectTeleportDestinationForm
{
    /** @var array */
    private $landList;
    /** @var string */
    private $inputData;

    public function __construct(array $landList, string $inputData)
    {
        $this->landList = $landList;
        $this->inputData = $inputData;
    }

    /**
     * フォームのコールバック関数を取得
     *
     * @return callable
     */
    private function getFunc(): callable
    {
        return function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }
            if ($data === 0) {
                AddTeleportDestinationForm::createUi($player);
                return;
            }

            $selectedLand = $this->landList[$data - 1];
            Main::getInstance()->getLandManager()->addTeleportList($player, $selectedLand);
            TeleportForm::createUi($player);
        };
    }

    /**
     * フォームを表示
     *
     * @param Main   $owner
     * @param Player $playuer
     * @return void
     */
    public function createUi(Player $player): void
    {
        $form = new SimpleForm(self::getFunc());
        $form->setTitle(Main::getMessage("add-teleport-select-title", [\count($this->landList)]));
        $form->setContent(Main::getMessage("add-teleport-select-label"));
        $form->addButton(Main::getMessage("button-back"));

        foreach ($this->landList as $land) {
            $message = TextFormat::BLACK . Main::getMessage("teleport-list-added-land", [$land[LandManager::Owner], $land[LandManager::ID]]);
            $buttonLabel = str_replace($this->inputData, TextFormat::DARK_PURPLE . $this->inputData . TextFormat::BLACK, $message);
            $form->addButton($buttonLabel);
        }

        $form->sendToPlayer($player);
    }
}
