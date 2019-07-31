<?php
namespace MoneySystemShop\form;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;

use MoneySystemShop\MoneySystemShop as Main;

class SendForm
{
	public $showing_form;

    public function sendForm(Player $player, array $data, bool $confirm = false)
    {
    	$name = $player->getName();
    	if (isset($this->showing_form[$name]))
    		return;
        $pk = new ModalFormRequestPacket();
        $pk->formId = $confirm ? $this->formid["BuyConfirm"] : $this->formid["OpenShop"];
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
