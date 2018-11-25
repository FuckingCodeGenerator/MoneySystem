<?php

/*
*  __  __       _                             __    ___    ___   _______
* |  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
* | |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
* | |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
* |_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
* All this program is made by hand of metowa1227.
* I certify here that all authorities are in metowa1227.
* Expiration date of certification: unlimited
* Secondary distribution etc are prohibited.
* The update is also done by the developer.
* This plugin is a developer API plugin to make it easier to write code.
* When using this plug-in, be sure to specify it somewhere.
* Warning if violation is confirmed.
*
* Developer: metowa1227
*/

/**
 * libform is a form manager plugin developed by yuko fuyutsuki.
 * Copyright (c) 2018 yuko fuyutsuki < https://github.com/fuyutsuki >
 * Copy of the MIT license:
 * < https://opensource.org/licenses/mit-license >
**/

namespace MoneySystemJob\form;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;

use MoneySystemJob\api\JobAPI;
use MoneySystemJob\form\CreateForm as Base;

use tokyo\pmmp\libform\{
    FormApi,
    element\Button,
    element\Dropdown,
    element\Label
};

use metowa1227\moneysystem\api\core\API;

class Receive
{
    public $response = null;

    public function receiveResponse(Player $player, ?int $key)
    {
        $api = JobAPI::getInstance();
        switch ($key) {
            default:
                return;
            case 1:
                if (!$api->getConfig()->get("Allow-change-job") && !empty($api->getJob($player))) {
                    $player->sendMessage(TextFormat::YELLOW . "一度職を決めたら変えることはできません。");
                    return;
                }
                FormApi::makeCustomForm(
                    function (Player $player, ?array $response) {
                        if (FormApi::formCancelled($response))
                            return;
                        $this->response[$player->getName()] = $response[0];
                        if (!JobAPI::getInstance()->getConfig()->get("Allow-change-job")) {
                            FormApi::makeModalForm(
                                function (Player $player, ?bool $response) {
                                    if (FormApi::formCancelled($response))
                                        return;
                                    if ($response) {
                                        if (JobAPI::getInstance()->joinJob($player, $this->response[$player->getName()]))
                                            $player->sendMessage(TextFormat::GREEN . "職に就きました。");
                                        else
                                            $player->sendMessage(TextFormat::RED . "エラーが発生しました。");
                                    }
                                    return;
                                }
                            )
                            ->setButtonText(true, "続行")
                            ->setButtonText(false, "キャンセル")
                            ->setContent(TextFormat::YELLOW . "[警告]\nこのサーバーでは、一度職に就くと二度と職業を変更すること、辞職することができなくなります！\nよく考えてから、慎重に決定してください！")
                            ->setTitle("就職 確認")
                            ->sendToPlayer($player);
                            return;
                        }
                        if (JobAPI::getInstance()->joinJob($player, $this->response[$player->getName()]))
                            $player->sendMessage(TextFormat::GREEN . "職に就きました。");
                        else
                            $player->sendMessage(TextFormat::RED . "エラーが発生しました。");
                        return;
                    }
                )
                ->addElement(new Dropdown("\n\n\n仕事を選択してください:", $api->getAllJobs(true)))
                ->setTitle("仕事に就く")
                ->sendToPlayer($player);
                return;

            case 2:
                if (!$api->getConfig()->get("Allow-change-job") && !empty($api->getJob($player))) {
                    $player->sendMessage(TextFormat::YELLOW . "一度就職したら二度と変えることはできません。");
                    return;
                }
                if (empty($api->getJob($player))) {
                    $player->sendMessage(TextFormat::YELLOW . "現在職に就いていません。");
                    return;
                }
                FormApi::makeModalForm(
                    function (Player $player, ?bool $response) {
                        if (FormApi::formCancelled($response))
                            return;
                        if ($response) {
                            if (JobAPI::getInstance()->resignJob($player))
                                $player->sendMessage(TextFormat::GREEN . "辞職しました。");
                            else
                                $player->sendMessage(TextFormat::RED . "エラーが発生しました。");
                        }
                        return;
                    }
                )
                ->setButtonText(true, "続行")
                ->setButtonText(false, "キャンセル")
                ->setContent("本当に辞職しますか？")
                ->setTitle("辞職 確認")
                ->sendToPlayer($player);
                return;

            case 3:
                $text = "";
                $list = $api->getAllJobs();
                $key = $api->getAllJobs(true);
                foreach ($key as $keys) {
                    $text .= "職業名: " . TextFormat::RED . TextFormat::BOLD . $keys . TextFormat::YELLOW . "\n\n内容:\n" . TextFormat::RESET;
                    foreach ($list as $key => $list1) {
                        $type = (strpos($key, "break") !== false) ? "破壊" : "設置";
                        foreach ($list1 as $job => $list2) {
                            if ($job !== $keys)
                                continue;
                            foreach ($list2 as $list3 => $list4) {
                                $item = Item::fromString($list3);
                                $name = $item->getName();
                                $text .= $name . "を"  . $type . "する [報酬: " . API::getInstance()->getUnit() . $list4 . " ]\n";
                            }
                        }
                    }
                    $text .= "\n\n";
                }
                FormApi::makeCustomForm()
                ->addElement(new Label($text))
                ->setTitle("職業リスト")
                ->sendToPlayer($player);
        }
    }
}
