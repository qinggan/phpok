<?php

namespace Yuansfer\Api;

use Yuansfer\Exception\InvalidParamException;


/**
 * Class Refund
 *
 * @package Yuansfer\Api
 * @author Feng Hao <flyinghail@msn.com>
 */
class SecurePayRefund extends AbstractApi
{
    const PASSWORD_COMMON = '@yuanex';

    public function __construct($yuansfer)
    {
        $this->addRequired(array('amount', 'reference'));

        parent::__construct($yuansfer);
    }

    protected function getPath()
    {
        return 'securepayRefund';
    }

    /**
     * @param number $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        if (!\is_numeric($amount)) {
            throw new InvalidParamException('The param `amount` is invalid in securepayRefund');
        }

        $this->params['amount'] = $amount;

        return $this;
    }

    /**
     * @param string $reference
     *
     * @return $this
     */
    public function setReference($reference)
    {
        if (!\preg_match('/^[a-zA-Z0-9_-]+$/', $reference)) {
            throw new InvalidParamException('The param `reference` is invalid in securepayRefund');
        }

        $this->params['reference'] = $reference;

        return $this;
    }

    /**
     * @param string $account
     * @param string $password
     *
     * @return $this
     */
    public function setStoreManager($account, $password)
    {
        $this->params['managerAccountNo'] = $account;
        $this->params['password'] = \md5(static::PASSWORD_COMMON . $password);

        $this->addRequired(array('managerAccountNo', 'password'));

        return $this;
    }
}