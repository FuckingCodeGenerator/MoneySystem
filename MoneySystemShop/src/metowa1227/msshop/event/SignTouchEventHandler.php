<?php
namespace metowa1227\msshop\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use pocketmine\block\BlockIds;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use pocketmine\tile\Sign;
use pocketmine\block\Air;
use pocketmine\item\Item;
use pocketmine\scheduler\Task;
use metowa1227\msshop\Main;
use metowa1227\msshop\jojoe77777\FormAPI\CustomForm;
use metowa1227\moneysystem\api\core\API;
use metowa1227\msshop\jojoe77777\FormAPI\ModalForm;

/**
 * プレイヤーが看板に触れたときに発生するイベントのハンドラ
 */
class SignTouchEventHandler implements Listener
{
    /** @var Main */
    private $owner;

    /**
     * 看板の Position
     *
     * @var Position
     */
    private $pos;

    /** @var array */
    private $shopData;

    /** @var array [Name => [Item, Price]] */
    private $buyData, $sellData;

    /** @var bool */
    private $flag;
    public function unsetFlag(): void { $this->flag = false; }

    public function __construct(Main $owner)
    {
        $this->owner = $owner;
    }

    public function handleEvent(PlayerInteractEvent $event): void
    {
        // アクションが右クリックか
        if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;

        if ($this->flag) return;

        $this->flag = true;

        $this->owner->getScheduler()->scheduleDelayedTask(
            new class($this) extends Task {
                private $owner;
                public function __construct(SignTouchEventHandler $owner) {
                    $this->owner = $owner;
                }
                public function onRun(int $tick): void {
                    $this->owner->unsetFlag();
                }
            }, 5
        );

        // 触れたブロックが看板かどうか
        if ($event->getBlock()->getId() !== BlockIds::STANDING_SIGN &&
            $event->getBlock()->getId() !== BlockIds::WALL_SIGN) return;

        $player = $event->getPlayer();
        $name = $player->getName();

        // 編集モードが有効なら, 編集画面を出す
        if ($this->owner->isEnabledEditMode($name) && $player->isOp()) {
            $this->pos = $event->getBlock();
            $this->openEditUI($player);
            return;
        }

        // SHOP かどうか
        $posStr = $this->owner->posToString($event->getBlock());
        $shopData = Main::getShopData();
        if (!isset($shopData[$posStr])) return;

        $shopData = $shopData[$posStr];

        // SHOP が有効かどうか
        if ($shopData['disable']) {
            $player->sendMessage(Main::getMessage('shop-disabled'));
            return;
        }

        // Form を展開
        $this->shopData[$name] = $shopData;
        if (!$shopData['disable_shop'] && !$shopData['disable_sell']) $this->openSwitchUI($player);
        elseif ($shopData['disable_sell']) $this->openBuyUI($shopData, $player);
        else $this->openSellUI($shopData, $player);
    }

    /**
     * SHOP, SELL の分岐画面
     *
     * @param Player $player
     * @return void
     */
    private function openSwitchUI(Player $player): void
    {
        $form = new ModalForm([$this, "receiveSwitchResponse"]);
        $form->setTitle("Buying and Selling");
        $form->setContent(Main::getMessage('form1-content'));
        $form->setButton1(Main::getMessage('form1-button1'));
        $form->setButton2(Main::getMessage('form1-button2'));
        $form->sendToPlayer($player);
    }

    /**
     * 分岐画面のレスポンスを受信
     *
     * @param Player $player
     * @param boolean $data
     * @return void
     */
    public function receiveSwitchResponse(Player $player, bool $data): void
    {
        if ($data) $this->openBuyUI($this->shopData[$player->getName()], $player);
        else $this->openSellUI($this->shopData[$player->getName()], $player);
    }
    
    /**
     * 編集画面の表示
     *
     * @param Player $player
     * @return void
     */
    private function openEditUI(Player $player): void
    {
        // プレイヤーが手に持っているアイテム
        $item = $player->getInventory()->getItemInHand();
        $itemId = $item->getId();
        $itemDamage = $item->getDamage();

        $data = [Main::getMessage('form-header', [$itemId, $itemDamage])];
        
        $shopData = Main::getShopData();
        $posStr = $this->owner->posToString($this->pos);
        if (isset($shopData[$posStr])) {
            $shopData = $shopData[$posStr];
            $data = array_merge($data, [
                $shopData['id'],
                $shopData['disable_shop'],
                $shopData['price'],
                $shopData['disable_sell'],
                $shopData['selling_price'],
                $shopData['comment'],
                $shopData['disable']
            ]);
        } else {
            $data = array_merge($data, [null, false, null, false, null, null, false]);
        }
        $this->sendForm($itemId, $itemDamage, $data, $player);
    }

    /**
     * 売却画面の表示
     *
     * @param array $shopData
     * @param Player $player
     * @param boolean $error
     * @return void
     */
    private function openSellUI(array $shopData, Player $player, bool $error = false): void
    {
        $itemId = $shopData['id'];
        $itemCount = $this->countInventoryItems($itemId, $player);
        $itemName = Item::fromString($itemId)->getName();

        $form = new CustomForm([$this, "receiveSellResponse"]);
        $form->setTitle("Sell Item");
        $form->addLabel(
            Main::getMessage('form4-header', [$itemName, $itemCount])
            . (($error) ? Main::getMessage('form4-format-wrong') : ""));
        $form->addLabel(Main::getMessage(
            'form4-price',
            [$itemName, API::getInstance()->getUnit(), $shopData['selling_price']]));
        $form->addInput(Main::getMessage('form4-count'));
        $form->sendToPlayer($player);
    }

    /**
     * 売却画面のレスポンスを受信
     *
     * @param Player $player
     * @param array|null $data
     * @return void
     */
    public function receiveSellResponse(Player $player, ?array $data): void
    {
        $name = $player->getName();
        if ($data === null || $data[2] == null) {
            unset($this->shopData[$name]);
            return;
        }

        $count = $data[2];
        // 入力形式が違うなら
        if (!$this->checkNumberFormat($count)) {
            $this->openSellUI($this->shopData[$name], $player, true);
            return;
        }

        // インベントリに存在するか
        $item = Item::fromString($this->shopData[$name]['id'])->setCount($count);
        if (!$player->getInventory()->contains($item)) {
            $player->sendMessage(Main::getMessage('lack-item'));
            unset($this->shopData[$name]);
            return;
        }

        $price = $this->shopData[$name]['selling_price'] * $count;

        // Form 表示
        $form = new ModalForm([$this, "receiveSellConfirmResponse"]);
        $form->setTitle("Sell Confirm");
        $form->setContent(
            Main::getMessage('form5-content',
            [$item->getName(), API::getInstance()->getUnit(), $price]));
        $form->setButton1(Main::getMessage('form5-button1'));
        $form->setButton2(Main::getMessage('form5-button2'));
        $form->sendToPlayer($player);
        $this->sellData[$name] = [$item, $price];
        unset($this->shopData[$name]);
    }

    /**
     * 売却の確認画面のレスポンスを受信
     *
     * @param Player $player
     * @param boolean $data
     * @return void
     */
    public function receiveSellConfirmResponse(Player $player, bool $data): void
    {
        $name = $player->getName();

        if ($data) {
            API::getInstance()->increase($player, $this->sellData[$name][1], "MoneySystemShop");
            $player->getInventory()->removeItem($this->sellData[$name][0]);
            $player->sendMessage(Main::getMessage('sold-item'));
        }

        unset($this->sellData[$name]);
    }

    /**
     * 購入画面の表示
     *
     * @param array $shopData
     * @param Player $player
     * @return void
     */
    private function openBuyUI(array $shopData, Player $player, bool $error = false): void
    {
        $itemId = $shopData['id'];
        $itemCount = $this->countInventoryItems($itemId, $player);
        $itemName = Item::fromString($itemId)->getName();

        $form = new CustomForm([$this, "receiveBuyResponse"]);
        $form->setTitle("Buy Item");
        $form->addLabel(
            Main::getMessage('form2-header', [$itemName, $itemCount])
            . (($error) ? Main::getMessage('form2-format-wrong') : ""));
        $form->addLabel(Main::getMessage(
            'form2-price',
            [$itemName, API::getInstance()->getUnit(), $shopData['price']]));
        $form->addInput(Main::getMessage('form2-count'));
        $form->sendToPlayer($player);
    }

    /**
     * 購入画面のレスポンスを受信
     *
     * @param Player $player
     * @param array|null $data
     * @return void
     */
    public function receiveBuyResponse(Player $player, ?array $data): void
    {
        $name = $player->getName();
        if ($data === null || $data[2] == null) {
            unset($this->shopData[$name]);
            return;
        }

        $count = $data[2];
        // 入力形式が違うなら
        if (!$this->checkNumberFormat($count)) {
            $this->openBuyUI($this->shopData[$name], $player, true);
            return;
        }

        // インベントリに追加可能か
        $item = Item::fromString($this->shopData[$name]['id'])->setCount($count);
        if (!$player->getInventory()->canAddItem($item)) {
            $player->sendMessage(Main::getMessage('inventory-full'));
            unset($this->shopData[$name]);
            return;
        }

        // 所持金が足りているか
        $price = $this->shopData[$name]['price'] * $count;
        if (API::getInstance()->get($player) < $price) {
            $player->sendMessage(Main::getMessage('lack-money'));
            unset($this->shopData[$name]);
            return;
        }

        // Form 表示
        $form = new ModalForm([$this, "receiveBuyConfirmResponse"]);
        $form->setTitle("Buy Confirm");
        $form->setContent(
            Main::getMessage('form3-content',
            [$item->getName(), API::getInstance()->getUnit(), $price]));
        $form->setButton1(Main::getMessage('form3-button1'));
        $form->setButton2(Main::getMessage('form3-button2'));
        $form->sendToPlayer($player);
        $this->buyData[$name] = [$item, $price];
        unset($this->shopData[$name]);
    }

    /**
     * 購入の確認画面のレスポンスを受信
     *
     * @param Player $player
     * @param boolean $data
     * @return void
     */
    public function receiveBuyConfirmResponse(Player $player, bool $data): void
    {
        $name = $player->getName();

        if ($data) {
            API::getInstance()->reduce($player, $this->buyData[$name][1], "MoneySystemShop");
            $player->getInventory()->addItem($this->buyData[$name][0]);
            $player->sendMessage(Main::getMessage('purchased-item'));
        }

        unset($this->buyData[$name]);
    }

    /**
     * インベントリにある特定のアイテム数をカウント
     *
     * @param string $itemId
     * @param Player $player
     * @return integer
     */
    private function countInventoryItems(string $itemId, Player $player): int
    {
        $result = 0;
        $inventory = $player->getInventory();
        for ($i = 0; $i < 36; $i++) {
            $item = $inventory->getItem($i);
            if ($item instanceof Air
                || ($item->getId() !== Item::fromString($itemId)->getId()
                || $item->getDamage() !== Item::fromString($itemId)->getDamage())) continue;
            $result += $item->getCount();
        }

        return $result;
    }

    /**
     * 編集画面のレスポンスを受信する
     *
     * @param Player $player
     * @param array|null $response
     * @return void
     */
    public function receiveEditResponse(Player $player, ?array $response): void
    {
        if ($response === null) return;

        // SHOPとSELLの両方がスキップされた場合
        if ($response[2] && $response[4]) return;

        // 入力された情報が正しいくない場合, 再入力
        if (!$this->checkFormat($response)) {
            // プレイヤーが手に持っているアイテム
            $item = $player->getInventory()->getItemInHand();
            $itemId = $item->getId();
            $itemDamage = $item->getDamage();

            $data = [
                Main::getMessage('form-header', [$itemId, $itemDamage]) . Main::getMessage('form-format-wrong'),
            ];
            for ($i = 1; $i < 8; $i++) $data[] = $response[$i];

            $this->sendForm($itemId, $itemDamage, $data, $player);
            return;
        }

        $sign = $this->pos->getLevel()->getTile($this->pos);
        if (!$sign instanceof Sign) return;

        if (!$response[2] && !$response[4]) {
            $sign->setText(
                (($response[7]) ? TextFormat::DARK_RED : TextFormat::GREEN)
                . TextFormat::BOLD . "[SHOP SELL]",
                TextFormat::AQUA . Item::fromString($response[1])->getName(),
                TextFormat::AQUA . "TOUCH TO OPEN",
                $response[6]
            );
            $sign->saveNBT();
        } elseif ($response[2]) {
            $sign->setText(
                (($response[7]) ? TextFormat::DARK_RED : TextFormat::YELLOW)
                . TextFormat::BOLD . "[SELL]",
                TextFormat::AQUA . Item::fromString($response[1])->getName(),
                TextFormat::AQUA . "TOUCH TO OPEN",
                $response[6]
            );
            $sign->saveNBT();
        } else {
            $sign->setText(
                (($response[7]) ? TextFormat::DARK_RED : TextFormat::LIGHT_PURPLE)
                . TextFormat::BOLD . "[SHOP]",
                TextFormat::AQUA . Item::fromString($response[1])->getName(),
                TextFormat::AQUA . "TOUCH TO OPEN",
                $response[6]
            );
            $sign->saveNBT();
        }

        $header = $this->owner->posToString($this->pos);
        $shopData = Main::getShopData();
        $content = [
            'id' => $response[1],
            'disable_shop' => $response[2],
            'price' => $response[3],
            'disable_sell' => $response[4],
            'selling_price' => $response[5],
            'comment' => $response[6],
            'disable' => $response[7]
        ];
        $shopData[$header] = $content;
        Main::setShopData($shopData);

        $player->sendMessage(Main::getMessage('shop-created'));
    }

    /**
     * 編集 Form をプレイヤーに送信
     *
     * @param integer $itemId
     * @param integer $itemDamage
     * @param array   $data [Header, ItemID, SkipShop, Price, SkipSell, SellPrice, Comment, Disable]
     * @param Player  $player
     * @return void
     */
    private function sendForm(int $itemId, int $itemDamage, array $data, Player $player): void
    {
        $form = new CustomForm([$this, 'receiveEditResponse']);
        $form->setTitle('EDIT SHOP');
        $form->addLabel($data[0]);
        $form->addInput(Main::getMessage('form-itemId'), $itemId . ':' . $itemDamage, $data[1]);
        $form->addToggle(Main::getMessage('form-skip-shop'), $data[2]);
        $form->addInput(Main::getMessage('form-price-shop'), "Price", $data[3]);
        $form->addToggle(Main::getMessage('form-skip-sell'), $data[4]);
        $form->addInput(Main::getMessage('form-price-sell'), "Price", $data[5]);
        $form->addInput(Main::getMessage('form-comment'), "", $data[6]);
        $form->addToggle(Main::getMessage('form-disable'), $data[7]);
        $form->sendToPlayer($player);
    }

    private function checkNumberFormat($data): bool
    {
        if (!ctype_digit($data) || intval($data) < 0) return false;

        return true;
    }

    private function checkFormat(array $data): bool
    {
        // ItemID
        try {
            Item::fromString($data[1]);
        } catch (\InvalidArgumentException $_) {
            return false;
        }

        // Price
        if ((!ctype_digit($data[3]) || intval($data[3]) < 0) && !$data[2]) return false;
        // Selling price
        if ((!ctype_digit($data[5]) || intval($data[5]) < 0) && !$data[4]) return false;

        return true;
    }
}
