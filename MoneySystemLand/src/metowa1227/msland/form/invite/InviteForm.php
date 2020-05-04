<?php
namespace metowa1227\msland\form\invite;

use metowa1227\msland\form\selector\LandSelector;
use metowa1227\msland\form\selector\PlayerSelector;
use metowa1227\msland\jojoe77777\FormAPI\ModalForm;
use metowa1227\msland\jojoe77777\FormAPI\SimpleForm;
use metowa1227\msland\land\LandManager;
use metowa1227\msland\Main;
use pocketmine\Player;
use pocketmine\Server;

class InviteForm
{
    private const INVITE_MENU_ADD = "invite-menu-add"; 
    private const INVITE_MENU_REMOVE = "invite-menu-remove";
    private const INVITE_MENU_LIST = "invite-menu-list";
    private const INVITE_MENU_PUBLICPLACE = "invite-menu-publicplace";

    public static function getFunc(): callable
    {
        return function (Player $player, ?int $data) {
            if ($data === null) {
                return;
            }

            $landId = LandSelector::getLandIdFromResult($player, $data);

            $form = new SimpleForm(self::getFunc2($landId));
            $form->setTitle("Invite Menu");
            $form->setContent(Main::getMessage("invite-menu-content"));
            $form->addButton(Main::getMessage(self::INVITE_MENU_ADD));
            $form->addButton(Main::getMessage(self::INVITE_MENU_REMOVE));
            $form->addButton(Main::getMessage(self::INVITE_MENU_LIST));
            $form->addButton(Main::getMessage(self::INVITE_MENU_PUBLICPLACE));
            $form->sendToPlayer($player);
        };
    }

    public static function getFunc2(int $landId): callable
    {
        return function (Player $player, ?int $data) use ($landId) {
            if ($data === null) {
                return;
            }

            switch ($data) {
                // 追加
                case 0:
                    $form = new PlayerSelector(self::getFunc3($landId, self::INVITE_MENU_ADD));
                    $form->showUi($player);
                break;
                // 削除
                case 1:
                    $form = new PlayerSelector(self::getFunc3($landId, self::INVITE_MENU_REMOVE));
                    $form->showUi($player, PlayerSelector::SEARCH_TYPE_INVITEE, $landId);
                break;
                // リスト
                case 2:
                    $invitees = [Main::getMessage("close")];
                    $invitees = \array_merge($invitees, Main::getInstance()->getLandManager()->getLandById($landId)[LandManager::Invitee]);

                    $form = new SimpleForm(self::getFunc5($landId, $invitees));
                    $form->setTitle("Invitee List");
                    $form->setContent(Main::getMessage("invite-list-content", [$landId]));
                    
                    foreach ($invitees as $invitee) {
                        $form->addButton($invitee);
                    }

                    $form->sendToPlayer($player);
                break;
                // 公共の土地
                case 3:
                    $form = new ModalForm(self::getFunc6($landId));
                    $form->setTitle("Public Place");
                    $form->setContent(Main::getMessage("invite-public-content", [$landId]));
                    $form->setButton1(Main::getMessage("yes"));
                    $form->setButton2(Main::getMessage("no"));
                    $form->sendToPlayer($player);
                break;
            }
        };
    }

    private static function getFunc3(int $landId, string $menuType): callable
    {
        return function (Player $player, ?string $data) use ($landId, $menuType) {
            if ($data === null) {
                return;
            }

            switch ($menuType) {
                case self::INVITE_MENU_ADD:
                    $form = new ModalForm(self::getFunc4($data, $landId, self::INVITE_MENU_ADD));
                    $form->setTitle("Invite to Land");
                    $form->setContent(Main::getMessage("invite-add-land-content", [$data]));
                    $form->setButton1(Main::getMessage("yes"));
                    $form->setButton2(Main::getMessage("no"));
                    $form->sendToPlayer($player);
                break;
                case self::INVITE_MENU_REMOVE:
                    $form = new ModalForm(self::getFunc4($data, $landId, self::INVITE_MENU_REMOVE));
                    $form->setTitle("Remove Invitation");
                    $form->setContent(Main::getMessage("invite-remove-land-content", [$data, $landId]));
                    $form->setButton1(Main::getMessage("yes"));
                    $form->setButton2(Main::getMessage("no"));
                    $form->sendToPlayer($player);
                break;
            }
        };
    }

    private static function getFunc4(string $target, int $landId, string $menuType): callable
    {
        return function (Player $player, bool $data) use ($target, $landId, $menuType) {
            switch ($menuType) {
                case self::INVITE_MENU_ADD:
                    if ($data) {
                        $target = Server::getInstance()->getOfflinePlayer($target);
                        Main::getInstance()->getLandManager()->addInvite($target, $landId);
                        $player->sendMessage(Main::getMessage("invite-add-success", [$target->getName(), $landId]));
                    }
                break;
                case self::INVITE_MENU_REMOVE:
                    if ($data) {
                        $target = Server::getInstance()->getOfflinePlayer($target);
                        Main::getInstance()->getLandManager()->removeInvite($target, $landId);
                        $player->sendMessage(Main::getMessage("invite-remove-success", [$target->getName()]));
                    }
                break;
            }
        };
    }

    private static function getFunc5(int $landId, array $invitees): callable
    {
        return function (Player $player, ?int $data) use ($landId, $invitees) {
            if ($data === null || $data === 0) {
                return;
            }

            $selected = $invitees[$data];
            $callable = self::getFunc3($landId, self::INVITE_MENU_REMOVE);
            $callable($player, $selected);
        };
    }

    private static function getFunc6(int $landId): callable
    {
        return function (Player $player, bool $data) use ($landId) {
            $str = $data ? "有効" : "無効";
            Main::getInstance()->getLandManager()->setPublicPlace($landId, $data);
            $player->sendMessage(Main::getMessage("invite-public-success", [$landId, $str]));
        };
    }
}
