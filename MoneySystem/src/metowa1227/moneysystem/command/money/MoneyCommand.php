<?php
namespace metowa1227\moneysystem\command\money;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use metowa1227\moneysystem\api\core\API;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class MoneyCommand extends Command
{
    private const CMD = "money";
    private const DESCRIPTION = "MoneySystem Command";
    private const USAGE = "/money help";

    public function __construct()
    {
        parent::__construct(self::CMD, self::DESCRIPTION, self::USAGE);
        $this->setPermission("moneysystem.command.money");
    }

    public function execute(CommandSender $sender, string $label, array $args) : bool
    {
        if (!isset($args[0])) {
            throw new InvalidCommandSyntaxException();
        }

        $api = API::getInstance();
        switch ($args[0]) {
            case "see":
                if (!isset($args[1])) {
                    $sender->sendMessage($api->getMessage("command.money.see.your-money.result", [$api->getUnit(), $api->get($sender)]));
                    return true;
                }

                $target = $args[1];
                if (!$api->exists($target)) {
                    $sender->sendMessage($api->getMessage("command.money.error.target-not-found", [$target]));
                    return false;
                }
                
                $sender->sendMessage($api->getMessage("command.money.see.result", [$target, $api->getUnit(), $api->get($target)]));
            break;
            
            case "pay":
                if (count($args) < 3) {
                    $sender->sendMessage("Usage: /" . self::CMD . " pay <target> <amount>");
                    return false;
                }

                if (!$this->checkInputs("pay", $args, $sender, $api)) {
                    return false;
                }

                $target = $args[1];
                $amount = intval($args[2]);
                if ($api->get($sender) < $amount) {
                    $sender->sendMessage($api->getMessage("command.money.error.lack-of-money"));
                    return false;
                }

                if (!$api->reduce($sender, $amount, "MoneySystem Pay", "Pay money")) {
                    $sender->sendMessage($api->getMessage("command.money.error"));
                    return false;
                }
                if (!$api->increase($target, $amount, "MoneySystem Pay", "Pay money")) {
                    $sender->sendMessage($api->getMessage("command.money.error"));
                    return false;
                }

                $sender->sendMessage($api->getMessage("command.money.pay.succeed.sender", [$target, $api->getUnit(), $amount]));
                if (($target = Server::getInstance()->getPlayer($target)) !== null) {
                    $target->sendMessage($api->getMessage("command.money.pay.succeed.target", [$sender->getName(), $api->getUnit(), $amount]));
                }
            break;

            case "rank":
                $page = "1";
                if (isset($args[1])) {
                    $page = $args[1];
                }

                if (!ctype_digit($page)) {
                    $sender->sendMessage($api->getMessage("command.money.error.input-must-be-integer"));
                    return false;
                }
                $page = intval($page);
                
                if (($allData = $api->getAll()) === null) {
                    $sender->sendMessage("NO RESULT: NO ACCOUNT EXISTS");
                    return false;
                }

                arsort($allData);
                $index = 0;
                $i = 0;
                $result = [];
                foreach ($allData as $player => $money) {
                    $result[$index][$player] = $money;
                    if (++$i >= 5) {
                        $index++;
                        $i = 0;
                    }
                }

                $totalPages = count($result);

                if ($totalPages < $page) {
                    $page = $totalPages;
                }

                $i = 1;
                $sender->sendMessage("MoneySystem Rank ----- " . TextFormat::ITALIC . TextFormat::GRAY . "Page " . $page . " of " . $totalPages);
                foreach ($result[$page - 1] as $player => $money) {
                    if (($rank = ($page - 1) * 5 + $i++) === 1) {
                        $orditinal = "st";
                    } else if ($rank === 2) {
                        $orditinal = "nd";
                    } else if ($rank === 3) {
                        $orditinal = "rd";
                    } else {
                        $orditinal = "th";
                    }
                    $op = Server::getInstance()->isOp($player) ? TextFormat::YELLOW . "[Operator] " . TextFormat::RESET : "";
                    $sender->sendMessage($rank . $orditinal . " > " . $op . $player . " [" . $api->getUnit() . $money . "]");
                }
            break;

            case "status":
                $totalMoney = 0;
                if ($sender->isOp()) {
                    foreach ($api->getAll() as $player => $money) {
                        $totalMoney += $money;
                    }

                    $status = $api->get($sender) / $totalMoney * 100;
                } else {
                    foreach ($api->getAll() as $player => $money) {
                        if (Server::getInstance()->isOp($player)) {
                            continue;
                        }
                        $totalMoney += $money;
                    }

                    $status = $api->get($sender) / $totalMoney * 100;
                }
                $sender->sendMessage($api->getMessage("command.money.status.result", [round($status, 2)]));
            break;

            case "give":
                if (!$this->testPermission($sender)) {
                    return false;
                }

                if (!$this->checkInputs("give", $args, $sender, $api)) {
                    return false;
                }

                $target = $args[1];
                $amount = $args[2];
        
                if (!$api->increase($target, $amount, "MoneySystem Give", "Give money")) {
                    $sender->sendMessage($api->getMessage("command.money.error"));
                    return false;
                }
        
                $sender->sendMessage($api->getMessage("command.money.give.succeed.sender", [$target, $api->getUnit(), $amount]));
            break;

            case "take":
                if (!$this->testPermission($sender)) {
                    return false;
                }

                if (!$this->checkInputs("take", $args, $sender, $api)) {
                    return false;
                }

                $target = $args[1];
                $amount = $args[2];
        
                if (!$api->reduce($target, $amount, "MoneySystem Take", "Take money")) {
                    $sender->sendMessage($api->getMessage("command.money.error"));
                    return false;
                }
        
                $sender->sendMessage($api->getMessage("command.money.take.succeed.sender", [$target, $api->getUnit(), $amount]));
            break;

            case "set":
                if (!$this->testPermission($sender)) {
                    return false;
                }

                if (!$this->checkInputs("set", $args, $sender, $api)) {
                    return false;
                }

                $target = $args[1];
                $amount = $args[2];
        
                if (!$api->set($target, $amount, "MoneySystem Set", "Set money")) {
                    $sender->sendMessage($api->getMessage("command.money.error"));
                    return false;
                }
        
                $sender->sendMessage($api->getMessage("command.money.set.succeed.sender", [$target, $api->getUnit(), $amount]));
            break;

            case "help":
                $sender->sendMessage("MoneySystem Commands");
                $sender->sendMessage("/money <action>...");
                $sender->sendMessage("actions:");
                $sender->sendMessage(" - see: Display your money");
                $sender->sendMessage(" - see <player>: Display player's money");
                $sender->sendMessage(" - pay <player> <amount>: Remit to player");
                $sender->sendMessage(" - rank <page = 1>: Display money ranking in the server");
                $sender->sendMessage(" - status: Display your status in the server");

                if ($sender->isOp()) {
                    $sender->sendMessage(" - give <player> <amount>: Give money to player");
                    $sender->sendMessage(" - take <player> <amount>: Forfeit money from players");
                    $sender->sendMessage(" - set <player> <amount>: Set player money");
                }
            break;

            default:
                throw new InvalidCommandSyntaxException();
            break;
        }

        return true;
    }

    /**
     * 入力データのチェック
     *
     * @param string $cmd
     * @param array $args
     * @param CommandSender $sender
     * @param API $api
     * @return boolean
     */
    private function checkInputs(string $cmd, array $args, CommandSender $sender, API $api): bool
    {
        if (count($args) < 3) {
            $sender->sendMessage("Usage: /" . self::CMD . " " . $cmd . " <target> <amount>");
            return false;
        }

        $target = $args[1];
        $amount = $args[2];
        if (!$api->exists($target)) {
            $sender->sendMessage($api->getMessage("command.money.error.target-not-found", [$target]));
            return false;
        }
        if (!ctype_digit($amount)) {
            $sender->sendMessage($api->getMessage("command.money.error.amount-must-be-integer"));
            return false;
        }

        $amount = intval($amount);
        if ($amount < 0) {
            $sender->sendMessage($api->getMessage("command.money.error.amount-less-than-zero"));
            return false;
        }

        return true;
    }
}
