<?php
namespace metowa1227\msland\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use metowa1227\msland\Main;
use metowa1227\msland\land\BuyLandProcess;
use metowa1227\msland\land\LandManager;

class FirstPosSetCommand extends Command
{
    private const CMD_COMMAND = "fp";
    private const CMD_DESCRIPTION = "購入する土地の最初の地点を設定";
    private const CMD_USAGE = "/fp";

    /** @var LandManager */
    private $landManager;
    /** @var Main */
    private $owner;

    public function __construct(Main $owner)
    {
        parent::__construct(self::CMD_COMMAND, self::CMD_DESCRIPTION, self::CMD_USAGE);
        $this->setPermission("msland.command.fp");
        $this->landManager = $owner->getLandManager();
        $this->owner = $owner;
    }

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if (!Main::isPlayer($sender)) {
            $sender->sendMessage($this->owner->getMessage("in-game-only"));
            return false;
        }
        if (\count($this->landManager->getLands($sender)) >= $this->owner->getConfigArgs()["limit"]) {
            $sender->sendMessage(Main::getMessage("land-limit-buy"));
            return false;
        }

        $name = $sender->getName();
        if (!isset(BuyLandProcess::getProcessingList()[$name])) {
            $buyLandProcess = new BuyLandProcess($sender);
        } else {
            $buyLandProcess = BuyLandProcess::getProcessingList()[$name];
        }
        if (($land = $this->landManager->getLandByPosition($sender->getPosition())) !== null) {
            $sender->sendMessage($this->owner->getMessage("land-already-bought", [$land[LandManager::Owner], $land[LandManager::ID]]));
            return false;
        }

        $buyLandProcess->setFirstPos($sender->getPosition());
        $sender->sendMessage($this->owner->getMessage("first-pos-saved"));
        return true;
    }
}
