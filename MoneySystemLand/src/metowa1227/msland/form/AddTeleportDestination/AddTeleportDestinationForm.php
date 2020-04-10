<?php
namespace metowa1227\msland\form\AddTeleportDestination;

use metowa1227\msland\jojoe77777\FormAPI\CustomForm;
use metowa1227\msland\land\LandManager;
use pocketmine\Player;
use metowa1227\msland\Main;

class AddTeleportDestinationForm
{    
    /**
     * フォームのコールバック関数を取得
     *
     * @return callable
     */
    private static function getFunc(): callable
    {
        return function (Player $player, ?array $data) {
            if ($data === null) {
                return;
            }
            if ($data[1] === "") {
                return;
            }

            $searchResult = self::search($data[1], $player);
            $selectForm = new SelectTeleportDestinationForm($searchResult, $data[1]);
            $selectForm->createUi($player);
        };
    }

    /**
     * フォームを表示
     *
     * @param Main   $owner
     * @param Player $playuer
     * @return void
     */
    public static function createUi(Player $player): void
    {
        $form = new CustomForm(self::getFunc());
        $form->setTitle("Add Teleport Destination");
        $form->addLabel(Main::getMessage("add-teleport-label"));
        $form->addInput(Main::getMessage("add-teleport-input-label"));
        $form->sendToPlayer($player);
    }

    /**
     * 土地の検索
     *
     * @param string|int $input
     * @param Player $player
     * @return array
     */
    private static function search($input, Player $player): array
    {
        $result = [];
        $hashTag = false;
        $landManager = Main::getInstance()->getLandManager();

        if ($input[0] === "#") {
            $input = \str_replace("#", "", $input);
            $hashTag = true;
        }
        if (\ctype_digit($input)) {
            $input = intval($input);
            if (($land = $landManager->getLandById($input)) !== null) {
                $result[] = $land;
            }
        }
        if ($hashTag) {
            return $result;
        }
        $input = (string) $input;
        foreach ($landManager->getLandData() as $land) {
            if (\strpos($land[LandManager::ID], $input) !== false) {
                $result[] = $land;
            }
            if (\strpos(\strtolower($land[LandManager::Owner]), \strtolower($input)) !== false) {
                $result[] = $land;
            }
        }
        
        $result = self::removeSameData($result);

        $i = 0;
        foreach ($result as $land) {
            foreach ($landManager->getTeleportList($player) as $tpList) {
                if ($land[LandManager::ID] === $tpList[LandManager::ID]) {
                    unset($result[$i]);
                }
            }
            if ($land[LandManager::Owner] === $player->getName()) {
                unset($result[$i]);
            }
            $i++;
        }

        return $result;
    }

    /**
     * 重複したデータの削除
     * 
     * @param array
     * @return array
     */
    private static function removeSameData(array $data): array
    {
        $i = 0;
        foreach ($data as $value1) {
            $n = 0;
            foreach ($data as $value2) {
                if ($i === $n) {
                    continue;
                }
                if ($value1 === $value2) {
                    unset($data[$i]);
                }
                $n++;
            }
            $i++;
        }

        return $data;
    }
}
