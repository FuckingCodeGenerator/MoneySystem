<?php

declare(strict_types=1);

namespace metowa1227\msland\land;

use InvalidArgumentException;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\level\Level;
use metowa1227\msland\Main;
use metowa1227\msland\land\BuyLandProcess;
use pocketmine\OfflinePlayer;

/**
 * 土地の基本管理を行う
 */
class LandManager
{
    /**
     * 土地データ
     *
     * @var array
     */
    private $lands;
    /** @var array */
    private $teleportList;
    /** @var Main */
    private $owner;

    /**
     * 土地データのキー
     */
    public const ID = "ID";
    public const StartX = "startX";
    public const EndX = "endX";
    public const StartZ = "startZ";
    public const EndZ = "endZ";
    public const StartY = "startY";
    public const EndY = "endY";
    public const Price = "price";
    public const Owner = "owner";
    public const Level = "level";
    public const Invitee = "invitee";
    public const IsPublic = "publicPlace";

    /**
     * @param Main $owner
     * @param array $landsData      土地データ
     * @param array $teleportList   プレイヤーのテレポート先一覧
     */
    public function __construct(Main $owner, array $landsData, array $teleportList)
    {
        $this->owner = $owner;
        $this->lands = $landsData;
        $this->teleportList = $teleportList;
    }

    public function getOwner(): Main
    {
        return $this->owner;
    }

    public function updateLandData(Main $_owner, array $landData): void
    {
        $this->lands = $landData;
    }

    /**
     * @return array 土地データ
     */
    public function getLandData(): array
    {
        return $this->lands;
    }

    /**
     * @return array テレポート先
     */
    public function getAllTeleportList(): array
    {
        return $this->teleportList;
    }

    /**
     * プレイヤーのテレポート先を取得
     *
     * @param Player $player
     * @return array
     */
    public function getTeleportList(Player $player): array
    {
        if (!isset($this->teleportList[$player->getName()])) {
            return [];
        }

        $result = [];
        foreach ($this->teleportList[$player->getName()] as $tpList) {
            $result[] = $this->getLandById($tpList);
        }
        
        return $result;
    }

    /**
     * プレイヤーのテレポート先を追加
     * 
     * @param Player $player
     * @param array $land
     */
    public function addTeleportList(Player $player, array $land): void
    {
        $this->teleportList[$player->getName()][] = $land[self::ID];
    }

    /**
     * 土地を削除
     *
     * @param integer $landId
     * @return boolean
     */
    public function removeLamd(int $landId): bool
    {
        if (!isset($this->lands[$landId])) {
            return false;
        }

        unset($this->lands[$landId]);
        return true;
    }

    /**
     * 土地の購入に必要な金額を取得
     *
     * @param BuyLandProcess $process
     * @return integer|null
     */
    public function getLandPrice(BuyLandProcess $process): ?int
    {
        $startPos = $process->getFirstPos();
        $endPos = $process->getSecondPos();
        if ($startPos === null || $endPos === null) {
            return null;
        }
        $config = $this->owner->getConfigArgs();
        $level = $startPos->getLevel()->getFolderName();

        // カスタムされた金額があるならば、置き換え
        if (count($config["custom-price"]) === 0) {
            $pricePerBlock = $config["default-price"];
        }
        foreach ($config["custom-price"] as $customPrice) {
            if (!in_array($level, array_keys($customPrice))) {
                $pricePerBlock = $config["default-price"];
            } else {
                $pricePerBlock = $customPrice[$level];
                break;
            }
        }

        // 3D判定が有効ならば高さも計算
        $diffY = max($startPos->y, $endPos->y) + 1 - min($startPos->y, $endPos->y);
        $blockCount = (max($startPos->x, $endPos->x) + 1 - min($startPos->x, $endPos->x)) * (max($startPos->z, $endPos->z) + 1 - min($startPos->z, $endPos->z));
        $result =  (int) $blockCount * $pricePerBlock * $diffY;

        return $result;
    }

    /**
     * プレイヤーが対象の土地の招待者かどうか
     *
     * @param array $land 対象の土地データ
     * @param Player $player
     * @return boolean
     */
    public function isInvitee(array $land, Player $player): bool
    {
        return in_array($player->getName(), $land[self::Invitee]);
    }

    /**
     * プレイヤーを土地に招待
     *
     * @param Player|OfflinePlayer $player
     * @param integer $landId
     * @return void
     */
    public function addInvite($player, int $landId): void
    {
        if (!$player instanceof Player && !$player instanceof OfflinePlayer) {
            throw new InvalidArgumentException("Specify a Player object or OfflinePlayer object as an argument");
        }

        if (!in_array($player->getName(), $this->getLandById($landId)[self::Invitee])) {
            $this->lands[$landId][self::Invitee][] = $player->getName();
        }
    }

    /**
     * 招待を削除
     *
     * @param Player|OfflinePlayer $player
     * @param integer $landId
     * @return void
     */
    public function removeInvite($player, int $landId): void
    {
        if (!$player instanceof Player && !$player instanceof OfflinePlayer) {
            throw new InvalidArgumentException("Specify a Player object or OfflinePlayer object as an argument");
        }

        if (in_array($player->getName(), array_values($this->lands[$landId][self::Invitee]))) {
            unset($this->lands[$landId][self::Invitee][\array_search($player->getName(), $this->lands[$landId][self::Invitee])]);
        }
    }

    /**
     * プレイヤーが対象の土地の所有者かどうか
     *
     * @param array $land 対象の土地データ
     * @param Player $player
     * @return boolean
     */
    public function isOwner(array $land, Player $player): bool
    {
        return $land[self::Owner] === $player->getName();
    }

    /**
     * 土地データをIDから取得
     *
     * @param integer $id
     * @return array|null 土地データが見つからなければnull
     */
    public function getLandById(int $id): ?array
    {
        foreach ($this->getLandData() as $land) {
            if (array_search($id, $land) !== false) {
                return $land;
            }
        }

        return null;
    }

    /**
     * 座標の土地データを取得
     *
     * @param Position $pos
     * @return array|null
     */
    public function getLandByPosition(Position $pos): ?array
    {
        if ($this->getLandData() === null) {
            return null;
        }
        $pos = Main::convertFloorPosition($pos);
        foreach ($this->getLandData() as $land) {
            $vec = $this->getMinMaxVec($land);
            // 座標判定にY座標を含めない場合
            if (!$this->owner->getConfigArgs()["enable-3d-judge"]) {
                $vec[self::Y_MIN] = 0;
                $vec[self::Y_MAX] = Level::Y_MAX;
            }
            // 土地の範囲内にいれば、土地データを返す
            if ((new AxisAlignedBB(
                $vec[self::X_MIN], $vec[self::Y_MIN], $vec[self::Z_MIN],
                $vec[self::X_MAX], $vec[self::Y_MAX], $vec[self::Z_MAX]))->isVectorInside($pos)) {
                return $land;
            }
        }

        return null;
    }

    /**
     * 最小座標と最大座標取得
     *
     * @param array $land 土地データ
     * @return array [minX, minY, minZ, maxX, maxY, maxZ] AxisAlignedBBのコンストラクタ基準
     */
    public const X_MIN = 0;
    public const Y_MIN = 1;
    public const Z_MIN = 2;
    public const X_MAX = 3;
    public const Y_MAX = 4;
    public const Z_MAX = 5;
    public function getMinMaxVec(array $land): array
    {
        $minY = min($land[self::StartY], $land[self::EndY]);
        $maxY = max($land[self::StartY], $land[self::EndY]);
        $minX = min($land[self::StartX], $land[self::EndX]);
        $maxX = max($land[self::StartX], $land[self::EndX]);
        $minZ = min($land[self::StartZ], $land[self::EndZ]);
        $maxZ = max($land[self::StartZ], $land[self::EndZ]);

        return [self::X_MIN => $minX - 1, self::Y_MIN => $minY - 1, self::Z_MIN => $minZ - 1,
            self::X_MAX => $maxX + 1, self::Y_MAX => $maxY + 1, self::Z_MAX => $maxZ + 1];
    }

    /**
     * 土地の中に誰かが所有している土地があるか
     *
     * @param Position $firstPos
     * @param Position $secondPos
     * @param Level $level
     * @return array|null 土地がある場合は土地データを、無ければnullを返す
     */
    public function existLandOwner(Position $firstPos, Position $secondPos, Level $level): ?array
    {
        $maxX = max($firstPos->x, $secondPos->x);
        $maxY = max($firstPos->y, $secondPos->y);
        $maxZ = max($firstPos->z, $secondPos->z);
        $minX = min($firstPos->x, $secondPos->x);
        $minY = min($firstPos->y, $secondPos->y);
        $minZ = min($firstPos->z, $secondPos->z);

        // 購入したい敷地内に既に所有者がいるかどうかの判定
        for ($x = $minX; $x <= $maxX; $x++) {
            for ($z = $minZ; $z <= $maxZ; $z++) {
                // 座標判定にY座標を含めない場合
                if (!$this->owner->getConfigArgs()["enable-3d-judge"]) {
                    $maxY = $minY + 1;
                }
                for ($y = $minY; $y <= $maxY; $y++) {
                    if (($land = $this->getLandByPosition(new Position($x, $y, $z, $level))) !== null) {
                        return $land;
                    }
                }
            }
        }

        return null;
    }

    /**
     * プレイヤーが所有する土地リストを取得
     *
     * @param Player|OfflinePlayer $player
     * @return array
     */
    public function getLands($player): array
    {
        if (!$player instanceof Player && !$player instanceof OfflinePlayer) {
            throw new InvalidArgumentException("Invalid argument to land information acquisition function");
        }

        $result = [];
        foreach ($this->getLandData() as $land) {
            if ($land[self::Owner] === $player->getName()) {
                $result[] = $land;
            }
        }

        return $result;
    }

    /**
     * 土地をデータに追加
     *
     * @param Player $player
     * @param BuyLandProcess $process
     * @return boolean|array 成功、失敗した場合はboolean, 既に所有者がいた場合は土地情報を返す
     */
    public function addLand(Player $player, BuyLandProcess $process)
    {
        $firstPos = $process->getFirstPos();
        $secondPos = $process->getSecondPos();

        if ($firstPos === null || $secondPos === null) {
            return false;
        }

        $level = $firstPos->getLevel();
        
        // 既に所有者がいるか
        if (($land = $this->existLandOwner($firstPos, $secondPos, $level)) !== null) {
            return $land;
        }

        // 土地のデータを作成
        $landId = $this->owner->getConfigArgs()["id"];
        $price = $this->getLandPrice($process);
        $landData = [
            self::ID => $landId,
            self::StartX => $firstPos->x,
            self::EndX => $secondPos->x,
            self::StartZ => $firstPos->z,
            self::EndZ => $secondPos->z,
            self::StartY => $firstPos->y,
            self::EndY => $secondPos->y,
            self::Price => $price,
            self::Owner => $player->getName(),
            self::Level => $level->getFolderName(),
            self::Invitee => [],
            self::IsPublic => false
        ];
        $this->lands[$landId] = $landData;
        $this->owner->updateLandId(++$landId);

        return true;
    }

    /**
     * 土地の所有者を変更
     * 
     * @param string $newOwner
     * @param int $landId
     */
    public function changeOwner(string $newOwner, int $landId): void
    {
        $land = $this->getLandById($landId);
        $land[self::Owner] = $newOwner;
        $this->lands[$landId] = $land;
    }

    /**
     * 公共の土地か
     *
     * @param integer $landId
     * @return boolean
     */
    public function isPublicPlace(int $landId): bool
    {
        return $this->getLandById($landId)[self::IsPublic];
    }

    /**
     * 土地を公共の土地にするかを設定
     *
     * @param integer $landId
     * @param boolean $value
     * @return void
     */
    public function setPublicPlace(int $landId, bool $value): void
    {
        $this->lands[$landId][self::IsPublic] = $value;
    }
}
