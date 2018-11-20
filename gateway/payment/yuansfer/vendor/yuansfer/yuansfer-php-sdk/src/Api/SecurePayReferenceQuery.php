<?php

namespace Yuansfer\Api;

use Yuansfer\Exception\InvalidParamException;

/**
 * Class SecurePayReferenceQuery
 *
 * @package Yuansfer\Api
 * @author  Feng Hao <flyinghail@msn.com>
 */
class SecurePayReferenceQuery extends AbstractApi
{
    public function __construct($yuansfer)
    {
        $this->addRequired(array('reference'));

        parent::__construct($yuansfer);
    }

    protected function getPath()
    {
        return 'securepay-reference-query';
    }

    /**
     * @param string $reference
     *
     * @return $this;
     */
    public function setReference($reference)
    {
        if (!\preg_match('/^[a-zA-Z0-9_-]+$/', $reference)) {
            throw new InvalidParamException('The param `reference` is invalid in securepay-reference-query');
        }

        $this->params['reference'] = $reference;

        return $this;
    }
}