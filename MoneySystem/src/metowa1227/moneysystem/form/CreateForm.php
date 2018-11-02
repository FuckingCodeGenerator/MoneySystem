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

/*
    Plugin description

    - CONTENTS
        - Lightweight, fast and multifunctional economic system.

    - AUTHOR
        - metowa1227 (MoneySystemAPI)

    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Pro 64bit
        - Intel(R) Core 2 Duo(TM) E8400 @ 3.00GHz
        - 8192MB DDR2 SDRAM PC2-5300(667MHz) , PC2-6400(800MHz)
        - Altay 3.0.6+dev for Minecraft: PE v1.5.0 (protocol version 274)
        - PHP 7.2.1 64bit supported version
*/

namespace metowa1227\moneysystem\form;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use metowa1227\moneysystem\api\core\API;

class CreateForm
{
    public function __construct(Player $sender, $path)
    {
        $this->sender = $sender;
        $this->id = new Config($path . "FormIDs.yml", Config::YAML);
    }

    public function send(Player $player, array $data, int $id) : void
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $id;
        $pk->formData = json_encode($data);
        $player->dataPacket($pk);
    }

    public function new()
    {
        $player = $this->sender;
        $name = $player->getName();
        if (!$player->isOp()) {
            $contents = array(
                $this->getMessage("close"),
                $this->getMessage("pay"),
                $this->getMessage("see"),
                $this->getMessage("all"),
                $this->getMessage("ranking"),
            );
            for ($i = 0; $i < 5; $i++) {
                $buttons[] = [
                    "text" => $contents[$i],
                ];
            }
        } else {
            $contents = array(
                $this->getMessage("close"),
                $this->getMessage("pay"),
                $this->getMessage("see"),
                $this->getMessage("all"),
                $this->getMessage("ranking"),
                $this->getMessage("increase"),
                $this->getMessage("reduce"),
                $this->getMessage("set"),
            );
            for ($i = 0; $i < 8; $i++) {
                $buttons[] = [
                    "text" => $contents[$i],
                ];
            }
        }
        $api = API::getInstance();
        $allMoney = 0;
        foreach ($api->getAll() as $key => $value) {
            if ($key === "CONSOLE" or Server::getInstance()->isOp($key))
                continue;
            $allMoney += $value["money"];
        }
        $status = 0;
        if ($allMoney > 0)
            $status = round((($api->get($name) / $allMoney) * 100), 2);
        $all = $api->getAll();
        $c = [];
        foreach ($all as $all => $value) {
            array_push($c, $value["money"]);
        }
        $all = $c;
        $max = 0;
        foreach ($all as $c) {
            $max += count($all);
        }
        rsort($all);
        foreach ($all as $key => $value) {
            if ($value == $api->get($name))
                $rank = ++$key;
        }
        $all = count($all);
        $data = [
            "type"    => "form",
            "title"   => TextFormat::GREEN . "MoneySystem",
            "content" => $this->getMessage("form.menu.info", [$api->getUnit(), $api->get($name), $status, $rank, $all, $this->getMessage("select")]),
            "buttons" => $buttons
        ];
        $this->send($player, $data, $this->id->get("menu"));
    }

    public function getMessage($name, $input = [])
    {
        return API::getInstance()->getMessage($name, $input);
    }
}
