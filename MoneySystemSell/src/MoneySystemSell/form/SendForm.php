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

namespace MoneySystemSell\form;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;

use MoneySystemSell\MoneySystemSell as Main;

class SendForm
{
	public $showing_form;

    public function sendForm(Player $player, array $data, bool $confirm = false)
    {
    	$name = $player->getName();
    	if (isset($this->showing_form[$name]))
    		return;
        $pk = new ModalFormRequestPacket();
        $pk->formId = $confirm ? $this->formid["SellConfirm"] : $this->formid["OpenSell"];
        $pk->formData = json_encode($data);
        $player->dataPacket($pk);
        $this->showing_form[$name] = true;
        Main::getTaskScheduler()->scheduleDelayedTask(
        	new class($this, $name) extends Task {
        		public function __construct(SendForm $class, string $name) {
        			$this->class = $class;
        			$this->name = $name;
        		}

        		public function onRun(int $tick) {
        			unset($this->class->showing_form[$this->name]);
        		}
        	}, 5
        );
    }
}
