<?php
namespace metowa1227\moneysystem\api\processor;

trait Check
{
	/**
	 * @param int $value
	 *
	 * @return int
	 */
    private function check($value) : int
    {
        return $value <= 0 ? 0 : $value;
    }
}
