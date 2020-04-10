<?php
namespace metowa1227\msland\form\selector;

class Selector
{
    /** @var callable */
    private $callable;

    public function __construct(callable $func)
    {
        $this->callable = $func;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }
}
