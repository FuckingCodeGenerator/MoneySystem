<?php
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
            if ($key === "CONSOLE" or Server::getInstance()->isOp($key)) {
                continue;
            }
            $allMoney += $value["money"];
        }
        $status = 0;
        if ($allMoney > 0) {
            $status = round((($api->get($name) / $allMoney) * 100), 2);
        }
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
            if ($value == $api->get($name)) {
                $rank = ++$key;
            }
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
