<?php

namespace Yuansfer\Api;

use Yuansfer\Exception\InvalidParamException;

/**
 * Class exchangeRate
 *
 * @package Yuansfer\Api
 * @author  Feng Hao <flyinghail@msn.com>
 */
class ExchangeRate extends AbstractApi
{
    public function __construct($yuansfer)
    {
        $this->addRequired(array(
            'date',
            'currency',
            'vendor',
        ));

        parent::__construct($yuansfer);
    }

    protected function getPath()
    {
        return 'exchangerate';
    }

    /**
     * @param string|\DateTime $date
     * @param string           $format
     *
     * @return $this
     */
    public function setDate($date, $format = 'Ymd')
    {
        if (!$date instanceof \DateTime) {
            $datetime = \DateTime::createFromFormat($format, $date);
            if ($datetime === false) {
                throw new InvalidParamException('The param `date` is invalid');
            }

            $date = $datetime;
        }


        $this->params['date'] = $date->format('Ymd');

        return $this;
    }

    /**
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->params['currency'] = \strtoupper($currency);

        return $this;
    }

    /**
     * @param string $vendor
     *
     * @return $this
     * @throws InvalidParamException
     */
    public function setVendor($vendor)
    {
        if (!\in_array($vendor, array('alipay', 'wechatpay', 'unionpay'), true)) {
            throw new InvalidParamException('The param `vender` is invalid in exchangerate');
        }

        $this->params['vendor'] = $vendor;

        return $this;
    }
}