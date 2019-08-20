## Language [English](#en) [日本語](#ja)

***
<a name="en"></a>
```This English text is machine translated by Google Translate.```<br>
# MoneySystem
A plug-in that adds economic elements to PocketMine-MP.<br>
## Download
[Download](http://metowa1227.s1001.xrea.com/downloadStorage/pmmp/moneysystem/)
## Commands
| Command | Desctiption | Usage |
---- | ---- | ----
| moneysystem | Displays MoneySystem information | /moneysystem |
## For developers
You can access MoneySystem using ```\moneysystem\api\core\API::getInstance()```
### Example
```php
// use moneysystem\api\core\API;
$result = API::getInstance()->increase($player, $amount);
```
***
<a name="ja"></a>
# MoneySystem
サーバーに経済要素を追加する PocketMine-MP 用のプラグインです。<br>
## ダウンロード
[ダウンロード](http://metowa1227.s1001.xrea.com/downloadStorage/pmmp/moneysystem/)
## コマンド
| コマンド | 説明 | 使用方法 |
---- | ---- | ----
| moneysystem | MoneySystem の情報を表示します | /moneysystem |
## 開発者へ
```\moneysystem\api\core\API::getInstance()``` でAPIにアクセスできます。
### 使用例
```php
// use moneysystem\api\core\API;
$result = API::getInstance()->increase($player, $amount);
```
