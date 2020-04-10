<?php
namespace metowa1227\msland\form\selector;

use metowa1227\moneysystem\api\core\API;
use pocketmine\Player;
use metowa1227\msland\Main;
use metowa1227\msland\jojoe77777\FormAPI\CustomForm;
use metowa1227\msland\land\LandManager;

class LandSelector extends Selector
{
    /**
     * 土地のセレクタUIを表示
     * 
     * @param Player $player
     */
    public function showUi(Player $player)
    {
        $api = API::getInstance();
        $lands = [Main::getMessage("dropdown-default")];
        foreach (Main::getInstance()->getLandManager()->getLands($player) as $land) {
            $lands[] = Main::getMessage("land-selector-dropdown-text", [$land[LandManager::ID], $api->getUnit(), $land[LandManager::Price]]);
        }

        $form = new CustomForm($this->getFunc());
        $form->setTitle("Select Land");
        $form->addLabel(Main::getMessage("land-selector-header"));
        $form->addDropdown(Main::getMessage("my-lands-dropdown"), $lands);
        $form->sendToPlayer($player);
    }

    private function getFunc(): callable
    {
        return function (Player $player, ?array $data) {
            if ($data === null) {
                return;
            }

            $callable = $this->getCallable();

            if ($data[1] === 0) {
                $callable($player, null);
                return;
            }

            $callable($player, $data[1]);
            return;
        };
    }

    public static function getLandIdFromResult(Player $player, int $index): int
    {
        return Main::getInstance()->getLandManager()->getLands($player)[$index - 1][LandManager::ID];
    }
}
