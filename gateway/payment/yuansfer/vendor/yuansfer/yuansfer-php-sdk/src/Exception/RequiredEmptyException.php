<?php

namespace Yuansfer\Exception;


class RequiredEmptyException extends \InvalidArgumentException implements YuansferException
{
    protected $api;

    protected $param;

    public function __construct($api, $param)
    {
        $this->api = $api;
        $this->param = $param;

        if (\is_array($param)) {
            $param = \implode('` / `', $param);
        }

        parent::__construct("The param `$param` is required in $api");
    }

    public function getParam()
    {
        return $this->param;
    }

    public function getApi()
    {
        return $this->api;
    }
}