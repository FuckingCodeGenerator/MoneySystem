<?php
namespace metowa1227\moneysystem\form;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use metowa1227\moneysystem\api\core\API;

class Received implements Listener
{
    public function __construct($path)
    {
        $this->id = new Config($path . "FormIDs.yml", Config::YAML);
    }

    public function send(Player $player, array $data, int $id) : void
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $id;
        $pk->formData = json_encode($data);
        $player->dataPacket($pk);
    }

    public function onReceived(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        $api    = API::getInstance();
        if ($packet instanceof ModalFormResponsePacket) {
            $player   = $event->getPlayer();
            $name     = $player->getName();
            $formId   = $packet->formId;
            $formData = json_decode($packet->formData, true);
            switch ($formId) {
                case $this->id->get("menu"):
                    switch ($formData) {
                        case 1:
                            $content = [
                                "type" => "input",
                                "text" => "\n" . $api->getMessage("pay.target") . "\n\n",
                                "placeholder" => "PlayerName",
                                "default" => ""
                            ];
                            $content2 = [
                                "type" => "input",
                                "text" => "\n" . $api->getMessage("pay.amount") . "\n\n",
                                "placeholder" => "Amount",
                                "default" => ""
                            ];
                            $data[][] = [];
                            $data["type"] = "custom_form";
                            $data["title"] = TextFormat::GREEN . "MoneySystem PAY";
                            $data["content"][] = $content;
                            $data["content"][] = $content2;
                            $this->send($player, $data, $this->id->get("pay"));
                            $this->pay = true;
                            return true;
                        case 2:
                            $content = [
                                "type" => "input",
                                "text" => "\n" . $api->getMessage("see.target") . "\n\n",
                                "placeholder" => "PlayerName",
                                "default" => ""
                            ];
                            $data[][] = [];
                            $data["type"] = "custom_form";
                            $data["title"] = TextFormat::GREEN . "MoneySystem SEE";
                            $data["content"][] = $content;
                            $this->send($player, $data, $this->id->get("see"));
                            $this->see = true;
                            return true;
                        case 3:
                            $result = "             ALL ACCOUNT DATA\n\n";
                            $i = 0;
                            foreach ($api->getAll() as $data) {
                                if (!$data)
                                    continue;
                                $result .= $data["name"] . " | " . $api->getUnit() . $api->get($data["name"]) . "\n";
                                $i += 1;
                            }
                            $content = [
                                "type" => "label",
                                "text" => $result
                            ];
                            $data["type"] = "custom_form";
                            $data["title"] = TextFormat::GREEN . "MoneySystem ALL";
                            $data["content"][] = $content;
                            $this->send($player, $data, $this->id->get("all"));
                            return true;
                        case 4:
                            $result = "                MONEY RANKING\n\n";
                            $i = 1;
                            $all = $api->getAll();
                            $data[] = "skip";
                            foreach ($all as $ac) {
                                $data[$ac["name"]] = $ac["money"];
                            }
                            arsort($data);
                            foreach ($data as $key => $value) {
                                if ($value === "skip" or Server::getInstance()->isOp($key) or $key === "CONSOLE") continue;
                                if ($key === $name)
                                    $result .= TextFormat::AQUA . $i . " | " . $name . " >>  " . TextFormat::YELLOW . $api->getUnit() . $value . "\n";
                                else
                                    $result .= TextFormat::BLACK . $i . " | " . $key . " >>  " . TextFormat::YELLOW . $api->getUnit() . $value . "\n";
                                $i++;
                            }
                            $content = [
                                "type" => "label",
                                "text" => $result
                            ];
                            $data["type"] = "custom_form";
                            $data["title"] = TextFormat::GREEN . "MoneySystem RANK";
                            $data["content"][] = $content;
                            $this->send($player, $data, $this->id->get("rank"));
                            return true;
                        case 5:
                            $content = [
                                "type" => "input",
                                "text" => "\n" . $api->getMessage("increase.target") . "\n\n",
                                "placeholder" => "PlayerName",
                                "default" => ""
                            ];
                            $content2 = [
                                "type" => "input",
                                "text" => "\n" . $api->getMessage("increase.amount") . "\n\n",
                                "placeholder" => "Amount",
                                "default" => ""
                            ];
                            $data[][] = [];
                            $data["type"] = "custom_form";
                            $data["title"] = TextFormat::AQUA . "MoneySystem INCREASE";
                            $data["content"][] = $content;
                            $data["content"][] = $content2;
                            $this->send($player, $data, $this->id->get("increase"));
                            $this->inc = true;
                            return true;
                        case 6:
                            $content = [
                                "type" => "input",
                                "text" => "\n" . $api->getMessage("reduce.target") . "\n\n",
                                "placeholder" => "PlayerName",
                                "default" => ""
                            ];
                            $content2 = [
                                "type" => "input",
                                "text" => "\n" . $api->getMessage("reduce.amount") . "\n\n",
                                "placeholder" => "Amount",
                                "default" => ""
                            ];
                            $data[][] = [];
                            $data["type"] = "custom_form";
                            $data["title"] = TextFormat::AQUA . "MoneySystem REDUCE";
                            $data["content"][] = $content;
                            $data["content"][] = $content2;
                            $this->send($player, $data, $this->id->get("reduce"));
                            $this->red = true;
                            return true;
                        case 7:
                            $content = [
                                "type" => "input",
                                "text" => "\n" . $api->getMessage("set.target") . "\n\n",
                                "placeholder" => "PlayerName",
                                "default" => ""
                            ];
                            $content2 = [
                                "type" => "input",
                                "text" => "\n" . $api->getMessage("set.amount") . "\n\n",
                                "placeholder" => "Amount",
                                "default" => ""
                            ];
                            $data[][] = [];
                            $data["type"] = "custom_form";
                            $data["title"] = TextFormat::AQUA . "MoneySystem SET";
                            $data["content"][] = $content;
                            $data["content"][] = $content2;
                            $this->send($player, $data, $this->id->get("set"));
                            $this->set = true;
                            return true;
                    }
                    break;

                case $this->id->get("pay"):
                    if (isset($this->pay)) {
                        unset($this->pay);
                        $target = $formData[0];
                        $amount = $formData[1];
                        if ($amount < 0 || $amount > MAX_MONEY || !is_numeric($amount) || !$api->exists($target) || $api->get($player) < $amount || preg_match('/^([1-9]\d*|0)\.(\d+)?$/', $amount)) {
                            $player->sendMessage($api->getMessage("pay.failed"));
                            return false;
                        }
                        $data = [
                            "type" => "modal",
                            "title" => TextFormat::GREEN . "MoneySystem PAY - CONFIRM -",
                            "content" => $api->getMessage("pay.confirm", [$target, $api->getUnit(), $amount]),
                            "button1" => $api->getMessage("continue"),
                            "button2" => $api->getMessage("cancel")
                        ];
                        $this->pay[$name]["target"] = $target;
                        $this->pay[$name]["amount"] = $amount;
                        $this->send($player, $data, $this->id->get("pay-send"));
                        $this->psend = true;
                        return true;
                    }
                    break;

                case $this->id->get("pay-send"):
                    if (isset($this->psend)) {
                        unset($this->psend);
                        if ($formData) {
                            $target = $this->pay[$name]["target"];
                            $amount = $this->pay[$name]["amount"];
                            if ($api->increase($target, $amount) && $api->reduce($player, $amount))
                                $player->sendMessage($api->getMessage("pay.success", [$target, $api->getUnit(), $amount]));
                            else
                                $player->sendMessage($api->getMessage("pay.failed"));
                            $exist = false;
                            foreach (Server::getInstance()->getOnlinePlayers() as $online) {
                                if ($online->getName() === $target)
                                    $exist = true;
                            }
                            if (!$exist)
                                $api->addCache($target, $name, $amount);
                            else
                                Server::getInstance()->getPlayer($target)->sendMessage($api->getMessage("pay.received", [$name, $api->getUnit(), $amount]));
                            unset($this->pay[$name]["target"], $this->pay[$name]["amount"]);
                            return true;
                        } else {
                            $player->sendMessage($api->getMessage("pay.cancel"));
                            unset($this->pay[$name]["target"], $this->pay[$name]["amount"]);
                            return true;
                        }
                    }
                    break;

                case $this->id->get("see"):
                    if (isset($this->see)) {
                        unset($this->see);
                        $target = $formData[0];
                        if ($target === "" or empty($target)) {
                            $player->sendMessage($api->getMessage("see.no-entry"));
                            return false;
                        }
                        if (!$api->exists($target)) {
                            $player->sendMessage($api->getMessage("see.notfound"));
                            return false;
                        }
                        $player->sendMessage($api->getMessage("see.result", [$target, $api->getUnit(), $api->get($target)]));
                        return true;
                    }
                    break;

                case $this->id->get("increase"):
                    if (isset($this->inc)) {
                        unset($this->inc);
                        $target = $formData[0];
                        $amount = $formData[1];
                        if ($amount < 0 || $amount > MAX_MONEY || !is_numeric($amount) || !$api->exists($target) || preg_match('/^([1-9]\d*|0)\.(\d+)?$/', $amount)) {
                            $player->sendMessage($api->getMessage("increase.failed"));
                            return false;
                        }
                        $this->add[$name]["target"] = $target;
                        $this->add[$name]["amount"] = $amount;
                        $data = [
                            "type" => "modal",
                            "title" => TextFormat::AQUA . "MoneySystem INCREASE - CONFIRM -",
                            "content" => $api->getMessage("increase.confirm", [$target, $api->getUnit(), $amount]),
                            "button1" => $api->getMessage("continue"),
                            "button2" => $api->getMessage("cancel")
                        ];
                        $this->send($player, $data, $this->id->get("increase-run"));
                        $this->incsend = true;
                        return true;
                    }
                    break;

                case $this->id->get("increase-run"):
                    if (isset($this->incsend)) {
                        unset($this->incsend);
                        if ($formData) {
                            $target = $this->add[$name]["target"];
                            $amount = $this->add[$name]["amount"];
                            if ($api->increase($target, $amount))
                                $player->sendMessage($api->getMessage("increase.success", [$target, $api->getUnit(), $amount]));
                            else
                                $player->sendMessage($api->getMessage("increase.failed"));
                            unset($this->add[$name]["target"], $this->add[$name]["amount"]);
                            return true;
                        } else {
                            $player->sendMessage($api->getMessage("increase.cancel"));
                            unset($this->add[$name]["target"], $this->add[$name]["amount"]);
                            return true;
                        }
                    }
                    break;

                case $this->id->get("reduce"):
                    if (isset($this->red)) {
                        unset($this->red);
                        $target = $formData[0];
                        $amount = $formData[1];
                        if ($amount < 0 || !is_numeric($amount) || !$api->exists($target) || preg_match('/^([1-9]\d*|0)\.(\d+)?$/', $amount)) {
                            $player->sendMessage($api->getMessage("reduce.failed"));
                            return false;
                        }
                        $this->take[$name]["target"] = $target;
                        $this->take[$name]["amount"] = $amount;
                        $data = [
                            "type" => "modal",
                            "title" => TextFormat::AQUA . "MoneySystem REDUCE - CONFIRM -",
                            "content" => $api->getMessage("reduce.confirm", [$target, $api->getUnit(), $amount]),
                            "button1" => $api->getMessage("continue"),
                            "button2" => $api->getMessage("cancel")
                        ];
                        $this->send($player, $data, $this->id->get("reduce-run"));
                        $this->redsend = true;
                        return true;
                    }
                    break;

                case $this->id->get("reduce-run"):
                    if (isset($this->redsend)) {
                        unset($this->redsend);
                        if ($formData) {
                            $target = $this->take[$name]["target"];
                            $amount = $this->take[$name]["amount"];
                            if ($api->reduce($target, $amount))
                                $player->sendMessage($api->getMessage("reduce.success", [$target, $api->getUnit(), $amount]));
                            else
                                $player->sendMessage($api->getMessage("reduce.failed"));
                            unset($this->take[$name]["target"], $this->take[$name]["amount"]);
                            return true;
                        } else {
                            $player->sendMessage($api->getMessage("reduce.cancel"));
                            unset($this->take[$name]["target"], $this->take[$name]["amount"]);
                            return true;
                        }
                    }
                    break;

                case $this->id->get("set"):
                    if (isset($this->set)) {
                        unset($this->set);
                        $target = $formData[0];
                        $amount = $formData[1];
                        if ($amount < 0 || !is_numeric($amount) || !$api->exists($target) || preg_match('/^([1-9]\d*|0)\.(\d+)?$/', $amount)) {
                            $player->sendMessage($api->getMessage("set.failed"));
                            return false;
                        }
                        $this->setting[$name]["target"] = $target;
                        $this->setting[$name]["amount"] = $amount;
                        $data = [
                            "type" => "modal",
                            "title" => TextFormat::AQUA . "MoneySystem SET - CONFIRM -",
                            "content" => $api->getMessage("set.confirm", [$target, $api->getUnit(), $amount]),
                            "button1" => $api->getMessage("continue"),
                            "button2" => $api->getMessage("cancel")
                        ];
                        $this->send($player, $data, $this->id->get("set-run"));
                        $this->setsend = true;
                        return true;
                    }
                    break;

                case $this->id->get("set-run"):
                    if (isset($this->setsend)) {
                        unset($this->setsend);
                        if ($formData) {
                            $target = $this->setting[$name]["target"];
                            $amount = $this->setting[$name]["amount"];
                            if ($api->set($target, $amount))
                                $player->sendMessage($api->getMessage("set.success", [$target, $api->getUnit(), $amount]));
                            else
                                $player->sendMessage($api->getMessage("set.failed"));
                            unset($this->setting[$name]["target"], $this->setting[$name]["amount"]);
                            return true;
                        } else {
                            $player->sendMessage($api->getMessage("set.cancel"));
                            unset($this->setting[$name]["target"], $this->setting[$name]["amount"]);
                            return true;
                        }
                    }
                    break;
            }
        }
    }
}
