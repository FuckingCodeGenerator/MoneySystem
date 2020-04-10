<?php
namespace metowa1227\msland\commands;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use metowa1227\msland\Main;
use metowa1227\msland\land\BuyLandProcess;
use metowa1227\msland\land\LandManager;

class SecondPosSetCommand extends Command
{
    private const CMD_COMMAND = "sp";
    private const CMD_DESCRIPTION = "購入する土地の次の地点を設定";
    private const CMD_USAGE = "/sp";

    /** @var LandManager */
    private $landManager;
    /** @var Main */
    private $owner;

    public function __construct(Main $owner)
    {
        parent::__construct(self::CMD_COMMAND,  self::CMD_DESCRIPTION, self::CMD_USAGE);
        $this->setPermission("msland.command.sp");
        $this->landManager = $owner->getLandManager();
        $this->owner = $owner;
    }

    public function execute(CommandSender $sender, string $label, array $args): bool
    {
        if (!Main::isPlayer($sender)) {
            $sender->sendMessage($this->owner->getMessage("in-game-only"));
            return false;
        }

        $name = $sender->getName();
        if (!isset(BuyLandProcess::getProcessingList()[$name])) {
            $buyLandProcess = new BuyLandProcess($sender);
        } else {
            $buyLandProcess = BuyLandProcess::getProcessingList()[$name];
        }

        if (!$this->checkCanBuy($buyLandProcess, $sender)) {
            return false;
        }

        $buyLandProcess->setSecondPos($sender->getPosition());
        $buyLandProcess->buyLand($sender, $this->owner);
        return true;
    }

    /**
     * 土地が購入可能か
     *
     * @param BuyLandProcess $buyLandProcess
     * @param Player $player
     * @return boolean
     */
    public function checkCanBuy(BuyLandProcess $buyLandProcess, Player $player): bool
    {
        if ($buyLandProcess->getFirstPos() === null) {
            $player->sendMessage($this->owner->getMessage("first-pos-not-set"));
            return false;
        }
        if (($land = $this->landManager->getLandByPosition($player->getPosition())) !== null) {
            $player->sendMessage($this->owner->getMessage("land-already-bought", [$land[LandManager::Owner], $land[LandManager::ID]]));
            return false;
        }

        return true;
    }
}
