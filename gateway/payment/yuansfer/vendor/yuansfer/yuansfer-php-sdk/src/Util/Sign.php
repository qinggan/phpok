<?php

namespace Yuansfer\Util;

/**
 * Class Sign
 *
 * @package Yuansfer
 * @author Feng Hao <flyinghail@msn.com>
 */
class Sign
{
    const KEY = 'verifySign';

    /**
     * @param array $params
     *
     * @return string
     */
    protected static function generate(&$params, $token)
    {
        unset($params[static::KEY]);

        \ksort($params, SORT_STRING);
        $str = '';
        foreach ($params as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }

        return \md5($str . \md5($token));
    }

    /**
     * @param array $params
     * @param string $token
     *
     * @return array
     */
    public static function append($params, $token)
    {
        $params[static::KEY] = static::generate($params, $token);

        return $params;
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public static function verify($params, $token)
    {
        if (!isset($params[static::KEY])) {
            return false;
        }

        $verifySign = $params[static::KEY];

        return $verifySign === static::generate($params, $token);
    }
}