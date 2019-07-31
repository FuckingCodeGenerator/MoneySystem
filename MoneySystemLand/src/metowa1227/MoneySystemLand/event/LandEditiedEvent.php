<?php
namespace metowa1227\MoneySystemLand\event;

use metowa1227\moneysystem\api\core\API;

use metowa1227\MoneySystemLand\MoneySystemLand;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\{ Server, Player };
use pocketmine\utils\{ Config, TextFormat };
use pocketmine\event\player\{ PlayerInteractEvent, PlayerJoinEvent };
use pocketmine\event\block\{ BlockBreakEvent, BlockPlaceEvent, SignChangeEvent };

class LandEditiedEvent implements \pocketmine\event\Listener
{
    public function __construct(MoneySystemLand $main)
    {
        $this->main = $main;
    }

    public function onTouch(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->isOp()) {
            return true;
        }
        if ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            $block = $event->getBlock()->getSide($event->getFace());
            $x = $block->getX();
            $z = $block->getZ();
            $level = $block->getLevel()->getFolderName();
            if ($this->main->config->exists("free")) {
                if (in_array($level, $this->main->config->get("free"))) {
                    return false;
                }
            }
            $info = $this->main->db->isProtected($x, $z, $level, $player);
            if (!$info) {
                $player->sendPopup(TextFormat::YELLOW . $this->main->getMessage($player->getName(), "need-buy"));
                $event->setCancelled();
                return false;
            }
            if (is_array($info)) {
                $player->sendPopup(
                    TextFormat::RED . str_replace(
                        array(
                            "--OWNER--",
                            "--ID--"
                        ),
                        array(
                            $info["owner"],
                            $info["ID"]
                        ),
                        $this->main->getMessage($player->getName(), "can-not-edit")
                    )
                );
                $event->setCancelled();
                return false;
            }
        }
    }

    public function onBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->isOp()) {
            return true;
        }
        $block = $event->getBlock();
        $x = $block->getX();
        $z = $block->getZ();
        $level = $block->getLevel()->getFolderName();
        if ($this->main->config->exists("free")) {
            if (in_array($level, $this->main->config->get("free"))) {
                return false;
            }
        }
        $info = $this->main->db->isProtected($x, $z, $level, $player);
        if (!$info) {
            $player->sendPopup(TextFormat::YELLOW . $this->main->getMessage($player->getName(), "need-buy"));
            $event->setCancelled();
            return false;
        }
        if (is_array($info)) {
            $player->sendPopup(
                TextFormat::RED . str_replace(
                    array(
                        "--OWNER--",
                        "--ID--"
                    ),
                    array(
                        $info["owner"],
                        $info["ID"]
                    ),
                    $this->main->getMessage($player->getName(), "can-not-edit")
                )
            );
            $event->setCancelled();
            return false;
        }
    }

    public function onPlace(BlockPlaceEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->isOp()) {
            return true;
        }
        $block = $event->getBlock();
        $x = $block->getX();
        $z = $block->getZ();
        $level = $block->getLevel()->getFolderName();
        if ($this->main->config->exists("free")) {
            if (in_array($level, $this->main->config->get("free"))) {
                return false;
            }
        }
        $info = $this->main->db->isProtected($x, $z, $level, $player);
        if (!$info) {
            $player->sendPopup(TextFormat::YELLOW . $this->main->getMessage($player->getName(), "need-buy"));
            $event->setCancelled();
            return false;
        }
        if (is_array($info)) {
            $player->sendPopup(
                TextFormat::RED . str_replace(
                    array(
                        "--OWNER--",
                        "--ID--"
                    ),
                    array(
                        $info["owner"],
                        $info["ID"]
                    ),
                    $this->main->getMessage($player->getName(), "can-not-edit")
                )
            );
            $event->setCancelled();
            return false;
        }
    }

    public function onReceived(DataPacketReceiveEvent $event)
    {
        $packet = $event->getPacket();
        if ($packet instanceof ModalFormResponsePacket) {
            $player   = $event->getPlayer();
            $formId   = $packet->formId;
            $formData = json_decode($packet->formData, true);
            switch ($formId) {
                case $this->main->menuid:
                    if ($formData === 1) {
                        $data = [
                            "type"    => "modal",
                            "title"   => TextFormat::AQUA . "Select Language",
                            "content" => "Please select the language to use in MoneySystemLand :",
                            "button1" => "日本語",
                            "button2" => "English"
                        ];
                        $this->main->send($player, $data, $this->main->selectlang);
                        $this->main->menu[$player->getName()] = true;
                        return true;
                    } elseif ($formData === 2) {
                        if (!isset($this->main->buy[$player->getName()])) {
                            $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $data = [
                                "type" => "form",
                                "title" => TextFormat::YELLOW . "Error",
                                "content" => "\n" . $this->main->getMessage($player->getName(), "scope") . "\n\n",
                                "buttons" => $buttons
                            ];
                            $this->main->send($player, $data, $this->main->scope);
                            return false;
                        } else {
                            $data = [
                                "type" => "modal",
                                "title" => TextFormat::GREEN . "Confirm",
                                "content" => str_replace(
                                    array(
                                        "--PRICE--",
                                        "--UNIT--"
                                    ),
                                    array(
                                        $this->main->buy[$player->getName()],
                                        API::getInstance()->getUnit()
                                    ),
                                    $this->main->getMessage($player->getName(), "buy-ready")
                                ),
                                "button1" => $this->main->getMessage($player->getName(), "buy"),
                                "button2" => $this->main->getMessage($player->getName(), "cancel")
                            ];
                            $this->main->send($player, $data, $this->main->ready);
                            $this->main->buycontinue[$player->getName()] = true;
                            return true;
                        }
                    } elseif ($formData === 3) {
                        if (
                            !isset($this->main->buy[$player->getName()]) &&
                            !isset($this->main->start[$player->getName()]) &&
                            !isset($this->main->end[$player->getName()])
                        ) {
                            $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $data = [
                                "type" => "form",
                                "title" => TextFormat::YELLOW . "Error",
                                "content" => "\n" . $this->main->getMessage($player->getName(), "scope2") . "\n\n",
                                "buttons" => $buttons
                            ];
                            $this->main->send($player, $data, $this->main->scope2);
                            return false;
                        } else {
                            unset(
                                $this->main->start[$player->getName()],
                                $this->main->end[$player->getName()],
                                $this->main->buy[$player->getName()]
                            );
                            $player->sendMessage(TextFormat::GREEN . $this->main->getMessage($player->getName(), "cancel-success"));
                            return true;
                        }
                    } elseif ($formData === 4) {
                        if (!$this->main->config->get("teleport")) {
                            $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $data = [
                                "type" => "form",
                                "title" => TextFormat::YELLOW . "Error",
                                "content" => "\n" . $this->main->getMessage($player->getName(), "tp-disabled") . "\n\n",
                                "buttons" => $buttons
                            ];
                            $this->main->send($player, $data, $this->main->tpdisabled);
                            return false;
                        } else {
                            $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $count = $this->main->db->getCounter();
                            $this->data[$player->getName()] = array();
                            for ($i = 1; $i < $count; $i++) {
                                if ($this->main->db->getLandById($i) !== null) {
                                    $buttons[] = [
                                        "text" => TextFormat::GREEN . "ID: " . $this->main->db->getLandById($i)["ID"] . " | " . $this->main->db->getLandById($i)["owner"]
                                    ];
                                } else {
                                    $buttons[] = [
                                        "text" => TextFormat::RED . "ID: " . $i
                                    ];
                                }
                            }
                            $data = [
                                "type" => "form",
                                "title" => TextFormat::AQUA . "MSLand Teleport System",
                                "content" => "\n" . $this->main->getMessage($player->getName(), "tp-select") . "\n\n",
                                "buttons" => $buttons
                            ];
                            $this->main->send($player, $data, $this->main->tp);
                            $this->tp[$player->getName()] = true;
                            return false;
                        }
                    } elseif ($formData === 5) {
                        if ($this->main->sell[$player->getName()]) {
                            $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $count = $this->main->db->getCounter();
                            $this->data[$player->getName()] = array();
                            $c = 1;
                            for ($i = 1; $i < $count; $i++) {
                                if ($this->main->db->getLandById($i) !== null) {
                                    if ($this->main->db->getLandById($i)["owner"] === $player->getName()) {
                                        $buttons[] = [
                                            "text" => TextFormat::GREEN . "ID: " . $this->main->db->getLandById($i)["ID"] . "Price: " . $this->main->db->getLandById($i)["price"]
                                        ];
                                        $c = ++$c;
                                        $this->id[$player->getName()][$c] = $this->main->db->getLandById($i)["ID"];
                                        $this->price[$player->getName()][$c] = $this->main->db->getLandById($i)["price"] / 2;
                                    } else {
                                        continue;
                                    }
                                } else {
                                    continue;
                                }
                            }
                            $data = [
                                "type" => "form",
                                "title" => TextFormat::AQUA . "Sale of land",
                                "content" => "\n" . $this->main->getMessage($player->getName(), "sell-select") . "\n\n",
                                "buttons" => $buttons
                            ];
                            $this->main->send($player, $data, $this->main->sellid);
                            $this->main->sell[$player->getName()] = false;
                            $this->sell[$player->getName()] = true;
                        }
                    } elseif ($formData === 6) {
                        if ($this->main->give[$player->getName()]) {
                            $buttons[] = [
                                    "text" => $this->main->getMessage($player->getName(), "continue")
                            ];
                            $buttons[] = [
                                    "text" => $this->main->getMessage($player->getName(), "cancel")
                            ];
                            $content = [
                                "type" => "input",
                                "text" => "\n" . $this->main->getMessage($player->getName(), "give-target") . "\n\n",
                                "placeholder" => "",
                                "default" => $player->getName()
                            ];
                            $content2 = [
                                "type" => "input",
                                "text" => "\n" . $this->main->getMessage($player->getName(), "give-id") . "\n\n",
                                "placeholder" => "",
                                "default" => "1"
                            ];
                            $content3 = [
                                "type" => "toggle",
                                "text" => $this->main->getMessage($player->getName(), "give-invite")
                            ];
                            $data[][] = [];
                            $data["type"] = "custom_form";
                            $data["title"] = TextFormat::GREEN . "Select target";
                            $data["buttons"] = $buttons;
                            $data["content"][] = $content;
                            $data["content"][] = $content2;
                            $data["content"][] = $content3;
                            $this->main->send($player, $data, $this->main->giveid);
                            $this->givecontinue[$player->getName()] = true;
                            $this->main->give[$player->getName()] = false;
                            return true;
                        }
                    } elseif ($formData === 7) {
                        if ($this->main->lists[$player->getName()]) {
                            $count = $this->main->db->getCounter();
                            $c = 1;
                            $list = TextFormat::AQUA . $this->main->getMessage($player->getName(), "list-title");
                            for ($i = 1; $i < $count; $i++) {
                                if ($this->main->db->getLandById($i) !== null) {
                                    if ($this->main->db->getLandById($i)["owner"] === $player->getName()) {
                                        if (count($this->main->db->getLandById($i)["invitee"]) === 0) {
                                            $invitee = "None";
                                        } else {
                                            $invitee = implode(array_keys($this->main->db->getLandById($i)["invitee"]), TextFormat::WHITE . ", " . TextFormat::GREEN);
                                        }
                                        $list .= "\n" . TextFormat::GREEN . "ID: " . $this->main->db->getLandById($i)["ID"] . "\n- Price: " . $this->main->db->getLandById($i)["price"] . "\n- World: " . $this->main->db->getLandById($i)["level"] . "\n- Invitee: " . $invitee;
                                    } else {
                                        continue;
                                    }
                                } else {
                                    continue;
                                }
                            }
                            $data = [
                                "type" => "modal",
                                "title" => TextFormat::AQUA . "Your land list",
                                "content" => $list,
                                "button1" => $this->main->getMessage($player->getName(), "close"),
                                "button2" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $this->main->send($player, $data, $this->main->list);
                            $this->main->lists[$player->getName()] = false;
                        }
                    } elseif ($formData === 8) {
                        if ($this->main->invites[$player->getName()]) {
                            $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $count = $this->main->db->getCounter();
                            $this->data[$player->getName()] = array();
                            $c = 1;
                            for ($i = 1; $i < $count; $i++) {
                                if ($this->main->db->getLandById($i) !== null) {
                                    if ($this->main->db->getLandById($i)["owner"] === $player->getName()) {
                                        $buttons[] = [
                                            "text" => TextFormat::GREEN . "ID: " . $this->main->db->getLandById($i)["ID"]
                                        ];
                                        $c = ++$c;
                                        $this->id3[$player->getName()][$c] = $this->main->db->getLandById($i)["ID"];
                                    } else {
                                        continue;
                                    }
                                } else {
                                    continue;
                                }
                            }
                            $data = [
                                "type" => "form",
                                "title" => TextFormat::AQUA . "Land share",
                                "content" => "\n" . $this->main->getMessage($player->getName(), "invite-select") . "\n\n",
                                "buttons" => $buttons
                            ];
                            $this->main->send($player, $data, $this->main->invite);
                            $this->main->invites[$player->getName()] = false;
                            $this->invite[$player->getName()] = true;
                            return true;
                        }
                    } elseif ($formData === 9) {
                        if ($this->main->unvites[$player->getName()]) {
                            $buttons[] = [
                                    "text" => $this->main->getMessage($player->getName(), "continue")
                            ];
                            $buttons[] = [
                                    "text" => $this->main->getMessage($player->getName(), "cancel")
                            ];
                            $content = [
                                "type" => "input",
                                "text" => "\n" . $this->main->getMessage($player->getName(), "invite-remove-target") . "\n\n",
                                "placeholder" => "",
                                "default" => $player->getName()
                            ];
                            $content2 = [
                                "type" => "input",
                                "text" => "\n" . $this->main->getMessage($player->getName(), "invite-remove-select") . "\n\n",
                                "placeholder" => "",
                                "default" => "1"
                            ];
                            $data[][] = [];
                            $data["type"] = "custom_form";
                            $data["title"] = TextFormat::AQUA . "Select target";
                            $data["buttons"] = $buttons;
                            $data["content"][] = $content;
                            $data["content"][] = $content2;
                            $this->main->send($player, $data, $this->main->unvite);
                            $this->unvite[$player->getName()] = true;
                            $this->main->give[$player->getName()] = false;
                            return true;
                        }
                    }
                    break;

                        /*if ($this->main->give[$player->getName()]) {
                            $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $count = $this->main->db->getCounter();
                            $this->data[$player->getName()] = array();
                            $c = 1;
                            for ($i = 1; $i < $count; $i++) {
                                if ($this->main->db->getLandById($i) !== null) {
                                    if ($this->main->db->getLandById($i)["owner"] === $player->getName()) {
                                        $buttons[] = [
                                            "text" => TextFormat::GREEN . "ID: " . $this->main->db->getLandById($i)["ID"] . "Price: " . $this->main->db->getLandById($i)["price"]
                                        ];
                                        $this->id2[$player->getName()][$i] = $this->main->db->getLandById($i)["ID"];
                                    } else {
                                        continue;
                                    }
                                } else {
                                    continue;
                                }
                            }
                            $data = [
                                "type" => "form",
                                "title" => TextFormat::AQUA . "Transfer of land",
                                "content" => "\n" . $this->main->getMessage($player->getName(), "give-select") . "\n\n",
                                "buttons" => $buttons
                            ];
                            $this->main->send($player, $data, $this->main->givetarget);
                            $this->main->give[$player->getName()] = false;
                            $this->give[$player->getName()] = true;
                        }
                    }
                    break;

                case $this->main->givetarget:
                    if ($this->give[$player->getName()]) {
                        $id = ++$formData;
                        $this->backup2[$player->getName()] = $id;
                        $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "continue")
                        ];
                        $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "cancel")
                        ];
                        $content = [
                            "type" => "input",
                            "text" => "\n" . $this->main->getMessage($player->getName(), "give-target") . "\n\n",
                            "placeholder" => "",
                            "default" => $player->getName()
                        ];
                        $content2 = [
                            "type" => "toggle",
                            "text" => $this->main->getMessage($player->getName(), "give-invite")
                        ];
                        $data[][] = [];
                        $data["type"] = "custom_form";
                        $data["title"] = TextFormat::GREEN . "Select target";
                        $data["buttons"] = $buttons;
                        $data["content"][] = $content;
                        $data["content"][] = $content2;
                        $this->main->send($player, $data, $this->main->giveid);
                        $this->givecontinue[$player->getName()] = true;
                        $this->give[$player->getName()] = false;
                        return true;
                    }
                    break;*/

                case $this->main->unvite:
                    if ($this->unvite[$player->getName()]) {
                        if ($this->main->db->isOwner($player->getName(), $formData[1])) {
                            $data = [
                                "type" => "modal",
                                "title" => TextFormat::GREEN . "Confirm",
                                "content" => str_replace(
                                    array(
                                        "--ID--",
                                        "--PLAYER--",
                                    ),
                                    array(
                                        $formData[1],
                                        $formData[0],
                                    ),
                                    $this->main->getMessage($player->getName(), "unvite-ready")
                                ),
                                "button1" => $this->main->getMessage($player->getName(), "invite-remove"),
                                "button2" => $this->main->getMessage($player->getName(), "cancel")
                            ];
                            $this->backup10[$player->getName()] = $formData[0];
                            $this->backup11[$player->getName()] = $formData[1];
                            $this->main->send($player, $data, $this->main->unvite3);
                            $this->unvite[$player->getName()] = false;
                            $this->unvite3[$player->getName()] = true;
                            return true;
                        } else {
                            $player->sendMessage(TextFormat::YELLOW . $this->main->getMessage($player->getName(), "not-owner"));
                            $this->unvite[$player->getName()] = false;
                            return false;
                        }
                    }
                    break;

                /*case $this->main->unvite2:
                    if ($this->unvite2[$player->getName()]) {
                        $data = [
                            "type" => "modal",
                            "title" => TextFormat::GREEN . "Confirm",
                            "content" => str_replace(
                                array(
                                    "--ID--",
                                    "--PLAYER--",
                                ),
                                array(
                                    $this->backup8[$player->getName()],
                                    $formData[0],
                                ),
                                $this->main->getMessage($player->getName(), "unvite-ready")
                            ),
                            "button1" => $this->main->getMessage($player->getName(), "invite-remove"),
                            "button2" => $this->main->getMessage($player->getName(), "cancel")
                        ];
                        $this->main->send($player, $data, $this->main->unvite3);
                        $this->backup9[$player->getName()] = $formData[0];
                        $this->unvite2[$player->getName()] = false;
                        $this->unvite3[$player->getName()] = true;
                        return true;
                    }
                    break;*/

                case $this->main->unvite3:
                    if ($this->unvite3[$player->getName()]) {
                        $this->main->db->removeInviteById(intval($this->backup11[$player->getName()]), $this->backup10[$player->getName()]);
                        $player->sendMessage(
                            TextFormat::GREEN . str_replace(
                                array(
                                    "--ID--",
                                    "--PLAYER--"
                                ),
                                array(
                                    $this->backup11[$player->getName()],
                                    $this->backup10[$player->getName()]
                                ),
                                $this->main->getMessage($player->getName(), "invite-remove-success")
                            )
                        );
                        $this->unvite3[$player->getName()] = false;
                        unset($this->backup10[$player->getName()], $this->backup11[$player->getName()]);
                        return true;
                    }
                    break;

                case $this->main->invite:
                    if ($this->invite[$player->getName()]) {
                        if ($formData === 0) {
                            $this->invite[$player->getName()] = false;
                            return true;
                        }
                        $formData = ++$formData;
                        $id = $this->id3[$player->getName()][$formData];
                        $this->backup6[$player->getName()] = $id;
                        $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "continue")
                        ];
                        $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "cancel")
                        ];
                        $content = [
                            "type" => "input",
                            "text" => "\n" . $this->main->getMessage($player->getName(), "invite-target") . "\n\n",
                            "placeholder" => "",
                            "default" => $player->getName()
                        ];
                        $data[][] = [];
                        $data["type"] = "custom_form";
                        $data["title"] = TextFormat::GREEN . "Select target";
                        $data["buttons"] = $buttons;
                        $data["content"][] = $content;
                        $this->main->send($player, $data, $this->main->invite2);
                        $this->invite[$player->getName()] = false;
                        $this->invite2[$player->getName()] = true;
                        return true;
                    }
                    break;

                case $this->main->invite2:
                    if ($this->invite2[$player->getName()]) {
                        $data = [
                            "type" => "modal",
                            "title" => TextFormat::GREEN . "Confirm",
                            "content" => str_replace(
                                array(
                                    "--ID--",
                                    "--PLAYER--",
                                ),
                                array(
                                    $this->backup6[$player->getName()],
                                    $formData[0],
                                ),
                                $this->main->getMessage($player->getName(), "invite-ready")
                            ),
                            "button1" => $this->main->getMessage($player->getName(), "invite"),
                            "button2" => $this->main->getMessage($player->getName(), "cancel")
                        ];
                        $this->backup7[$player->getName()] = $formData[0];
                        $this->main->send($player, $data, $this->main->invite3);
                        $this->invite2[$player->getName()] = false;
                        $this->invite3[$player->getName()] = true;
                        return true;
                    }
                    break;

                case $this->main->invite3:
                    if ($this->invite3[$player->getName()]) {
                        if (!$formData) {
                            $this->invite3[$player->getName()] = false;
                            return true;
                        }
                        $this->main->db->addInviteById(intval($this->backup6[$player->getName()]), $this->backup7[$player->getName()]);
                        $player->sendMessage(
                            TextFormat::GREEN . str_replace(
                                array(
                                    "--ID--",
                                    "--PLAYER--"
                                ),
                                array(
                                    $this->backup6[$player->getName()],
                                    $this->backup7[$player->getName()]
                                ),
                                $this->main->getMessage($player->getName(), "invite-success")
                            )
                        );
                        $this->invite3[$player->getName()] = false;
                        unset($this->backup6[$player->getName()], $this->backup7[$player->getName()]);
                        return true;
                    }
                    break;

                case $this->main->giveid:
                    if ($this->givecontinue[$player->getName()]) {
                        if ($this->main->db->isOwner($player->getName(), $formData[1])) {
                            if ($formData[2]) {
                                $invite = $this->main->getMessage($player->getName(), "yes");
                            } else {
                                $invite = $this->main->getMessage($player->getName(), "no");
                            }
                            $data = [
                                "type" => "modal",
                                "title" => TextFormat::GREEN . "Confirm",
                                "content" => str_replace(
                                    array(
                                        "--ID--",
                                        "--PLAYER--",
                                        "--INVITE--"
                                    ),
                                    array(
                                        $formData[1],
                                        $formData[0],
                                        $invite
                                    ),
                                    $this->main->getMessage($player->getName(), "give-ready")
                                ),
                                "button1" => $this->main->getMessage($player->getName(), "transfer"),
                                "button2" => $this->main->getMessage($player->getName(), "cancel")
                            ];
                            $this->backup3[$player->getName()] = $formData[0];
                            $this->backup4[$player->getName()] = $formData[1];
                            $this->backup5[$player->getName()] = $formData[2];
                            $this->main->send($player, $data, $this->main->ready3);
                            $this->givecontinue[$player->getName()] = false;
                            $this->ready3[$player->getName()] = true;
                            return true;
                        } else {
                            $player->sendMessage(TextFormat::YELLOW . $this->main->getMessage($player->getName(), "not-owner"));
                            $this->givecontinue[$player->getName()] = false;
                            unset($this->backup2[$player->getName()]);
                            return false;
                        }
                    }
                    break;

                case $this->main->ready3:
                    if ($this->ready3[$player->getName()]) {
                        if (!$formData) {
                            $this->ready3[$player->getName()] = false;
                            return true;
                        }
                        $this->main->db->giveLand(intval($this->backup4[$player->getName()]), $this->backup3[$player->getName()], $player->getName(), $this->backup5[$player->getName()]);
                        $player->sendMessage(
                            TextFormat::GREEN . str_replace(
                                array(
                                    "--ID--",
                                    "--PLAYER--"
                                ),
                                array(
                                    $this->backup4[$player->getName()],
                                    $this->backup3[$player->getName()]
                                ),
                                $this->main->getMessage($player->getName(), "give-success")
                            )
                        );
                        $this->ready3[$player->getName()] = false;
                        unset($this->backup2[$player->getName()], $this->backup3[$player->getName()], $this->backup4[$player->getName()], $this->backup5[$player->getName()]);
                        return true;
                    }
                    break;

                case $this->main->sellid:
                    if ($this->sell[$player->getName()]) {
                        if ($formData === 0) {
                            $this->sell[$player->getName()] = false;
                            return true;
                        }
                        $formData = ++$formData;
                        $this->backup[$player->getName()] = $formData;
                        $data = [
                            "type" => "modal",
                            "title" => TextFormat::GREEN . "Confirm",
                            "content" => str_replace(
                                array(
                                    "--ID--",
                                    "--UNIT--",
                                    "--PRICE--"
                                ),
                                array(
                                    $this->id[$player->getName()][$formData],
                                    API::getInstance()->getUnit(),
                                    $this->price[$player->getName()][$formData]
                                ),
                                $this->main->getMessage($player->getName(), "sell-ready")
                            ),
                            "button1" => $this->main->getMessage($player->getName(), "sale"),
                            "button2" => $this->main->getMessage($player->getName(), "cancel")
                        ];
                        $this->main->send($player, $data, $this->main->ready2);
                        $this->sellcontinue[$player->getName()] = true;
                        $this->sell[$player->getName()] = false;
                        return true;
                    }
                    break;

                case $this->main->ready2:
                    if ($this->sellcontinue[$player->getName()]) {
                        if (!$formData) {
                            $this->sellcontinue[$player->getName()] = false;
                            return true;
                        }
                        $data = $this->backup[$player->getName()];
                        API::getInstance()->increase($player, $this->price[$player->getName()][$data], "土地の売却");
                        $this->main->db->sellLandById($this->id[$player->getName()][$data]);
                        $player->sendMessage(
                            TextFormat::GREEN . str_replace(
                                array(
                                    "--ID--",
                                    "--UNIT--",
                                    "--PRICE--"
                                ),
                                array(
                                    $this->id[$player->getName()][$data],
                                    API::getInstance()->getUnit(),
                                    $this->price[$player->getName()][$data]
                                ),
                                $this->main->getMessage($player->getName(), "sell-success")
                            )
                        );
                        $this->sellcontinue[$player->getName()] = false;
                        unset($this->id[$player->getName()], $this->price[$player->getName()]);
                        return true;
                    }
                    break;

                case $this->main->tp:
                    if ($this->tp[$player->getName()]) {
                        if ($formData === 0) {
                            $this->tp[$player->getName()] = false;
                            return true;
                        }
                        $count = count($this->main->db->getAllIds()) + 1;
                        $id = $formData;
                        if ($id === null) {
                            $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $data = [
                                "type" => "form",
                                "title" => TextFormat::YELLOW . "Error",
                                "content" => "\n" . $this->main->getMessage($player->getName(), "tp-offline") . "\n\n",
                                "buttons" => $buttons
                            ];
                            $this->main->send($player, $data, $this->main->tpoffline);
                            $this->tp[$player->getName()] = false;
                            return false;
                        }
                        if ($this->main->db->getLandById($id) !== null) {
                            $this->main->tp($player, $id);
                        } else {
                            $buttons[] = [
                                "text" => $this->main->getMessage($player->getName(), "close")
                            ];
                            $data = [
                                "type" => "form",
                                "title" => TextFormat::YELLOW . "Error",
                                "content" => "\n" . $this->main->getMessage($player->getName(), "tp-offline") . "\n\n",
                                "buttons" => $buttons
                            ];
                            $this->main->send($player, $data, $this->main->tpoffline);
                            return false;
                        }
                        $this->tp[$player->getName()] = false;
                    }
                    break;

                case $this->main->selectlang:
                    if ($this->main->menu[$player->getName()]) {
                        if (!$formData) {
                            $this->main->lang->set($player->getName(), "English");
                            $this->main->lang->save();
                            $player->sendMessage(TextFormat::GREEN . "Set it to English.");
                            $this->main->menu[$player->getName()] = false;
                            return true;
                        } else {
                            $this->main->lang->set($player->getName(), "Japanese");
                            $this->main->lang->save();
                            $player->sendMessage(TextFormat::GREEN . "日本語に設定しました。");
                            $this->main->menu[$player->getName()] = false;
                            return true;
                        }
                    }
                    break;

                case $this->main->ready:
                    if ($this->main->buycontinue[$player->getName()]) {
                        if (!$formData) {
                            $player->sendMessage($this->main->getMessage($player->getName(), "buy-cancelled"));
                            $this->main->buycontinue[$player->getName()] = false;
                            return true;
                        } else {
                            $startp = $this->main->start[$player->getName()];
                            $endp   = $this->main->end[$player->getName()];
                            $startX = floor($startp["x"]);
                            $endX   = floor($endp["x"]);
                            $startZ = floor($startp["z"]);
                            $endZ   = floor($endp["z"]);
                            if ($startX > $endX) {
                                $backup = $startX;
                                $startX = $endX;
                                $endX   = $backup;
                            }
                            if ($startZ > $endZ) {
                                $backup = $startZ;
                                $startZ = $endZ;
                                $endZ   = $backup;
                            }
                            $land = $this->main->db->here($startX, $endX, $startZ, $endZ, $player->getLevel()->getFolderName());
                            if ($land) {
                                $player->sendMessage(
                                    TextFormat::YELLOW . str_replace(
                                        "--OWNER--",
                                        $land["owner"],
                                        $this->main->getMessage($name, "already")
                                    )
                                );
                                return false;
                            }
                            if ($this->main->config->exists("disable-levels")) {
                                if (in_array($player->getLevel()->getFolderName(), $this->main->config->get("disable-levels"))) {
                                    $player->sendMessage($this->main->getMessage($player->getName(), "disable-levels"));
                                    $this->main->buycontinue[$player->getName()] = false;
                                    return true;
                                }
                            }
                            if ($this->main->config->get("limit") !== -1) {
                                if (count($this->main->db->getLands($player->getName())) <= $this->main->config->get("limit")) {
                                    if (API::getInstance()->get($player) < $this->main->buy[$player->getName()]) {
                                        $player->sendMessage($this->main->getMessage($player->getName(), "no-money"));
                                        $this->main->buycontinue[$player->getName()] = false;
                                        return true;
                                    }
                                    API::getInstance()->reduce($player, $this->main->buy[$player->getName()], "土地の購入");
                                    $this->main->db->addLand($startX, $endX, $startZ, $endZ, $player->getLevel()->getFolderName(), $this->main->buy[$player->getName()], $player->getName());
                                    $player->sendMessage(
                                        TextFormat::GREEN . str_replace(
                                            array(
                                                "--UNIT--",
                                                "--PRICE--",
                                                "--ID--"
                                            ),
                                            array(
                                                API::getInstance()->getUnit(),
                                                $this->main->buy[$player->getName()],
                                                $this->main->config->get("id")
                                            ),
                                            $this->main->getMessage($player->getName(), "bought-land")
                                        )
                                    );
                                    unset(
                                        $this->main->start[$player->getName()],
                                        $this->main->end[$player->getName()],
                                        $this->main->buy[$player->getName()]
                                    );
                                    $this->main->buycontinue[$player->getName()] = false;
                                    return true;
                                } else {
                                    $player->sendMessage($this->main->getMessage($player->getName(), "land-limit"));
                                    $this->main->buycontinue[$player->getName()] = false;
                                    return true;
                                }
                            }
                        }
                    }
                    break;

                default:
                    return true;
                    break;
            }
        }
    }
}
