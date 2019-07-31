<?php
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
