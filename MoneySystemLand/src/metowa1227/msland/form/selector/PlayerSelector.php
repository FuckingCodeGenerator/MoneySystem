<?php
namespace metowa1227\msland\form\selector;

use metowa1227\moneysystem\api\core\API;
use pocketmine\Player;
use metowa1227\msland\jojoe77777\FormAPI\CustomForm;
use metowa1227\msland\jojoe77777\FormAPI\SimpleForm;
use metowa1227\msland\land\LandManager;
use metowa1227\msland\Main;

class PlayerSelector extends Selector
{
    /** @var int 検索対象 */
    public const SEARCH_TYPE_DEFAULT = 0;
    public const SEARCH_TYPE_INVITEE = 1;

    /** @var array */
    private $playerList;
    /** @var int */
    private $searchType;

    /**
     * プレイヤーのセレクタUIを表示
     *
     * @param Player $player
     * @param integer $searchType
     * @param $data
     * @return void
     */
    public function showUi(Player $player, int $searchType = self::SEARCH_TYPE_DEFAULT, $data = null)
    {
        $this->searchType = $searchType;
        $playerList = [Main::getMessage("dropdown-default")];

        switch ($searchType) {
            case self::SEARCH_TYPE_DEFAULT:
                foreach (Main::getInstance()->getServer()->getOnlinePlayers() as $online) {
                    if ($online->getName() === $player->getName()) {
                        continue;
                    }
                    $playerList[] = $online->getName();
                }
            break;
            case self::SEARCH_TYPE_INVITEE:
                $playerList = \array_merge($playerList, Main::getInstance()->getLandManager()->getLandById($data)[LandManager::Invitee]);
            break;
        }
        $this->playerList = $playerList;

        $dropdownHeader = ($searchType === self::SEARCH_TYPE_DEFAULT) ? Main::getMessage("online-players") : Main::getMessage("invitee-players");
        $form = new CustomForm($this->getFunc());
        $form->setTitle("Select Player");
        $form->addLabel(Main::getMessage("player-selector-header"));
        $form->addDropdown($dropdownHeader, $this->playerList);
        $form->addInput(Main::getMessage("search-player"));
        $form->sendToPlayer($player);
    }

    private function getFunc(): callable
    {
        return function (Player $player, ?array $data) {
            $callable = $this->getCallable();

            if ($data === null) {
                $callable($player, null);
                return;
            }

            if ($data[1] !== 0) {
                $callable($player, $this->playerList[$data[1]]);
                return;
            }
            if ($data[2] !== null) {
                $searchResult = $this->search($data[2], $player->getName());
                $form = new SimpleForm($this->getSearchFunc($searchResult));
                $form->setTitle("Search Result");
                $form->setContent(Main::getMessage("player-search-content", [\count($searchResult) - 1]));
                
                foreach ($searchResult as $result) {
                    $form->addButton($result);
                }

                $form->sendToPlayer($player);
                return;
            }

            $callable($player, null);
        };
    }
    
    private function getSearchFunc(array $database): callable
    {
        return function (Player $player, ?int $data) use ($database) {
            $callable = $this->getCallable();

            if ($data === null) {
                $callable($player, null);
                return;
            }

            if ($data === 0) {
                $this->showUi($player);
                return;
            }

            $callable($player, $database[$data]);
        };
    }

    /**
     * プレイヤーを検索
     * 
     * @param string $keyword
     * @param string $excluded 除外対象
     * @return array
     */
    private function search(string $keyword, string $excluded): array
    {
        $result = [Main::getMessage("button-back")];

        if (empty($keyword)) {
            return $result;
        }

        $allPlayers = ($this->searchType === self::SEARCH_TYPE_DEFAULT) ? API::getInstance()->getAll(true) : $this->playerList;

        foreach ($allPlayers as $player) {
            if (\strpos($player, $keyword) !== false && $player !== $excluded) {
                $result[] = $player;
            }
        }

        return $result;
    }
}
