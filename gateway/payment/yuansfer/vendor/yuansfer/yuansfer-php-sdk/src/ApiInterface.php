<?php

namespace Yuansfer;

use Yuansfer\Exception\YuansferException;

/**
 * Interface ApiInterface
 *
 * @package Yuansfer
 * @author Feng Hao <flyinghail@msn.com>
 */
interface ApiInterface
{
    const VERSION = 'v2';

    /**
     * @return string
     * @throws YuansferException
     */
    public function send();
}