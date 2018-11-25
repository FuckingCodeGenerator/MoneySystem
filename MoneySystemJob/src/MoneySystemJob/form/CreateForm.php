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

use MoneySystemJob\api\JobAPI;
use MoneySystemJob\form\Receive;

use tokyo\pmmp\libform\{
    FormApi,
    element\Button
};

class CreateForm
{
    public static $form;

    public function openForm(Player $player)
    {
        $name = $player->getName();
        $job = JobAPI::getInstance()->getJob($player);
        if ($job === null)
            $job = "無職";
        self::$form = FormApi::makeListForm([new Receive(), "receiveResponse"])
        ->setContent("現在の職業: " . $job . "\n\n選択してください:")
        ->addButton(new Button("閉じる"))
        ->addButton(new Button("仕事に就く"))
        ->addButton(new Button("仕事を辞める"))
        ->addButton(new Button("仕事リスト"))
        ->setTitle(TextFormat::RED . "MoneySystemJob")
        ->sendToPlayer($player);
    }
}
