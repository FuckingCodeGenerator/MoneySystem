<?php

/*
* __  __       _                             __    ___    ___   _______
*|  \/  | ___ | |_  ___   _    _  ____  _   |  |  / _ \  / _ \ |___   /
*| |\/| |/ _ \| __|/ _ \ | |  | |/  _ \/ /  |  | |_// / |_// /    /  /
*| |  | |  __/| |_| (_) || |__| || (_)   |  |  |   / /_   / /_   /  /
*|_|  |_|\___| \__|\___/ |__/\__||____/\_\  |__|  /____| /____| /__/
*
*All this program is made by hand of metowa 1227.
*I certify here that all authorities are in metowa 1227.
*Expiration date of certification: unlimited
*Secondary distribution etc are prohibited.
*The update is also done by the developer.
*This plugin is a developer API plugin to make it easier to write code.
*When using this plug-in, be sure to specify it somewhere.
*Warning if violation is confirmed.
*
*Developer: metowa 1227
*Development Team: metowa 1227 Plugin Development Team (Members: metowa 1227 only)
*/

/*
    PluginIntrodtion
    - CONTENTS
        - MoneySystemAPI's land protector
    - AUTHOR
        - metowa1227 (MoneySystemAPI)
    - DEVELOPMENT ENVIRONMENT
        - Windows 10 Pro 64bit
        - Intel(R) Core 2 Duo(TM) E8400 @ 3.00GHz
        - 8192MB DDR2 SDRAM PC2-5300(667MHz) , PC2-6400(800MHz)
        - 1.7dev-1001「[REDACTED]」Minecraft PE v1.4.0用実装APIバージョン3.0.0-ALPHA12(プロトコルバージョン261)
        - PHP 7.2.1 64bit supported version
        - MoneySystemAPI (SYSTEM) version 12.11 package version 12.00 API version 11.1 GREEN PAPAYA OX4 Edition (Released date: 2018/06/13)
*/

namespace metowa1227\MoneySystemLand;

use metowa1227\moneysystem\api\core\API;

use metowa1227\MoneySystemLand\{
    MSLand,
    event\LandEditiedEvent,
    database\DataManager
};

use pocketmine\plugin\PluginBase;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\plugin\MethodEventExecutor;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\{ Player, Server };
use pocketmine\utils\{ Config, TextFormat };
use pocketmine\event\{ Listener, EventPriority };
use pocketmine\command\{ Command, CommandSender };

class MoneySystemLand extends PluginBase implements MSLand, Listener
{
    /* @var int */
    public $menuid = 235897564;
    public $selectlang = 213486123;
    public $buyland = 2595683456;
    public $scope = 23465656354;
    public $scope2 = 29293465834;
    public $ready = 2834525452;
    public $buycancel = 1283425642;
    public $tpdisabled = 8234564235;
    public $tp = 234523857;
    public $tpoffline = 6523485283;
    public $sellid = 782427854;
    public $ready2 = 3456346478;
    public $ready3 = 3486326348;
    public $givetarget = 46456423;
    public $giveid = 644566834;
    public $list = 624684837;
    public $invite = 4686854;
    public $invite2 = 654682346;
    public $invite3 = 265476487;
    public $unvite = 564848476;
    public $unvite2 = 76245245;
    public $unvite3 = 865482758;

    public function send(Player $player, array $data, int $id) : void
    {
        $pk = new ModalFormRequestPacket();
        $pk->formId = $id;
        $pk->formData = json_encode($data);
        $player->dataPacket($pk);
    }

    public function getDefaultLang(string $name)
    {
        if (!$this->lang->exists($name)) {
            return "English";
        }

        $lang = $this->lang->get($name);

        return $lang;
    }

    public function getMessage(string $name, $message)
    {
        $lang = $this->getDefaultLang($name);
        switch ($lang) {
            case "Japanese":
                if (!$this->ld[0]->exists($message)) {
                    return "Message convert error: " . $message;
                }
                return $this->ld[0]->get($message);
                break;

            case "English":
                if (!$this->ld[1]->exists($message)) {
                    return "Message convert error: " . $message;
                }
                return $this->ld[1]->get($message);
                break;

            default:
                if (!$this->ld[1]->exists($message)) {
                    return "Message convert error: " . $message;
                }
                return $this->ld[1]->get($message);
                break;
        }
    }

    public function tp(Player $player, int $id)
    {
        $land = $this->db->getLandById($id);
        $level = Server::getInstance()->getLevelByName($land["level"]);
        $x = (int) ($land["startX"] + (($land["endX"] - $land["startX"]) / 2));
        $z = (int) ($land["startZ"] + (($land["endZ"] - $land["startZ"]) / 2));
        $cnt = 0;
        for ($y = 128; ; $y--) {
            $vec = new Vector3($x, $y, $z);
            if ($level->getBlock($vec)->isSolid()) {
                $y++;
                break;
            }
            if ($cnt === 5) {
                break;
            }
            if ($y <= 0) {
                ++$cnt;
                ++$x;
                --$z;
                $y = 128;
                continue;
            }
        }
        $player->teleport(new Position($x + 0.5, $y + 0.1, $z + 0.5, $level));
        $player->sendMessage(TextFormat::GREEN . $this->getMessage($player->getName(), "tp-success"));
        return true;

    }

    public function onEnable()
    {
        $this->saveResource("jpn.yml");
        $this->saveResource("eng.yml");
        $this->saveResource("Config.yml");
        Server::getInstance()->getPluginManager()->registerEvents(new LandEditiedEvent($this), $this);
        $this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML);
        $this->land   = new Config($this->getDataFolder() . "Lands.yml", Config::YAML);
        $this->lang   = new Config($this->getDataFolder() . "Lang.yml", Config::YAML);
        $this->ld[0]  = new Config($this->getDataFolder() . "jpn.yml", Config::YAML);
        $this->ld[1]  = new Config($this->getDataFolder() . "eng.yml", Config::YAML);
        $this->db = new DataManager($this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        $name = $sender->getName();
        switch ($command->getName()) {
            case "land":
                if ($sender instanceof Player) {
                    $this->openMenu($sender);
                    return true;
                } else {
                    $this->getLogger()->info($this->getMessage($name, "in-game-only"));
                    return true;
                }
                break;

            case "s":
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->getMessage($name, "in-game-only"));
                    return true;
                }
                $x = floor($sender->x);
                $z = floor($sender->z);
                $level = $sender->getLevel()->getFolderName();
                $this->start[$name] = array("x" => $x, "z" => $z, "level" => $level);
                $sender->sendMessage($this->getMessage($name, "s-saved"));
                return true;
                break;

            case "e":
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->getMessage($name, "in-game-only"));
                    return true;
                }

                if (!isset($this->start[$name])) {
                    $sender->sendMessage($this->getMessage($name, "s-not-set"));
                    return true;
                }

                if ($sender->getLevel()->getFolderName() !== $this->start[$name]["level"]) {
                    $sender->sendMessage($this->getMessage($name, "level-diff"));
                    return true;
                }

                $startX = $this->start[$sender->getName()]["x"];
                $startZ = $this->start[$sender->getName()]["z"];
                $endX = floor($sender->x);
                $endZ = floor($sender->z);
                $this->end[$name] = array(
                    "x" => $endX,
                    "z" => $endZ
                );
                if ($startX > $endX) {
                    $temp = $endX;
                    $endX = $startX;
                    $startX = $temp;
                }
                if ($startZ > $endZ) {
                    $temp = $endZ;
                    $endZ = $startZ;
                    $startZ = $temp;
                }
                $startX--;
                $endX++;
                $startZ--;
                $endZ++;
                $land = $this->db->here($startX, $endX, $startZ, $endZ, $sender->getLevel()->getFolderName());
                if ($land !== false) {
                    $sender->sendMessage(
                        TextFormat::YELLOW . str_replace(
                            "--OWNER--",
                            $land["owner"],
                            $this->getMessage($name, "already")
                        )
                    );
                    unset($this->end[$name]);
                    return true;
                }
                $price = (($endX - $startX) - 1) * (($endZ - $startZ) - 1) * $this->config->get("price");
                $count = count($this->config->get("customPrice"));
                for ($i = 0; $i < $count; $i++) {
                    if (isset($this->config->get("customPrice")[$i][$sender->getLevel()->getFolderName()])) {
                        $price = (($endX - $startX) - 1) * (($endZ - $startZ) - 1) * $this->config->get("customPrice")[$i][$sender->getLevel()->getFolderName()];
                    }
                }
                $this->buy[$name] = $price;
                $sender->sendMessage($this->getMessage($name, "e-saved"));
                $sender->sendMessage(
                    str_replace(
                        array(
                            "--UNIT--",
                            "--PRICE--"
                        ),
                        array(
                            API::getInstance()->getUnit(),
                            $price
                        ),
                        $this->getMessage($name, "buy-confirm")
                    )
                );
                return true;
                break;

            case "ltp":
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->getMessage($name, "in-game-only"));
                    return true;
                }

                if (!$this->config->get("teleport")) {
                    $sender->sendMessage(TextFormat::YELLOW . $this->getMessage($name, "tp-disabled"));
                    return true;
                }

                if (!isset($args[0]) or $args[0] === "") {
                    $sender->sendMessage(TextFormat::YELLOW . $this->getMessage($name, "tp-id-not-set"));
                    return true;
                }

                if ($this->db->getLandById($args[0]) === null) {
                    $sender->sendMessage(TextFormat::YELLOW . $this->getMessage($name, "tp-dest-not-found"));
                    return true;
                }

                $this->tp($sender, $args[0]);
                $sender->sendMessage(TextFormat::GREEN . $this->getMessage($name, "tp-success"));
                return true;
                break;

            case "here":
                if (!$sender instanceof Player) {
                    $sender->sendMessage($this->getMessage($name, "in-game-only"));
                    return true;
                }

                $result = $this->db->here2($sender->x, $sender->z, $sender->getLevel()->getFolderName(), $sender);
                if (!$result) {
                    $sender->sendMessage($this->getMessage($name, "no-owner"));
                } else {
                    $sender->sendMessage(
                        str_replace(
                            array(
                                "--OWNER--",
                                "--ID--",
                                "--UNIT--",
                                "--PRICE--"
                            ),
                            array(
                                $result["owner"],
                                $result["ID"],
                                API::getInstance()->getUnit(),
                                $result["price"]
                            ),
                            $this->getMessage($name, "info")
                        )
                    );
                }
                return true;
                break;
        }
    }

    public function openMenu(Player $player) : void
    {
        $name = $player->getName();
        $contents = array(
            $this->getMessage($name, "close"),
            $this->getMessage($name, "select-lang"),
            $this->getMessage($name, "buy-land"),
            $this->getMessage($name, "buy-stop"),
            $this->getMessage($name, "teleport"),
            $this->getMessage($name, "sell"),
            $this->getMessage($name, "give"),
            $this->getMessage($name, "list"),
            $this->getMessage($name, "invite"),
            $this->getMessage($name, "invite-remove")
        );
        for ($i = 0; $i < 10; $i++) {
            $buttons[] = [
                "text" => $contents[$i],
            ];
        }
        $data = [
            "type"    => "form",
            "title"   => TextFormat::AQUA . "MoneySystemLand",
            "content" => "\n" . $this->getMessage($name, "select") . " :\n\n",
            "buttons" => $buttons
        ];
        $this->sell[$name] = true;
        $this->give[$name] = true;
        $this->lists[$name] = true;
        $this->invites[$name] = true;
        $this->unvites[$name] = true;
        $this->send($player, $data, $this->menuid);
    }
}
