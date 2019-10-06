<?php
namespace metowa1227\moneysystem\api\traits;

trait CheckMoneyTrait
{
	/**
	 * 所持金が0未満かどうかを判定し、0未満ならば0に設定します
	 *
	 * @param integer $value
	 * @return integer
	 */
    private function check(int $value) : int
    {
        return $value <= 0 ? 0 : $value;
    }
}
