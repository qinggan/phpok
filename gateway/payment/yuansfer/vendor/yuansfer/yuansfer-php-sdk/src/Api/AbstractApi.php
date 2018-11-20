<?php

namespace Yuansfer\Api;

use Httpful\Httpful;
use Httpful\Mime;
use Httpful\Request;
use Httpful\Handlers\FormHandler;
use Httpful\Handlers\JsonHandler;
use Httpful\Exception\ConnectionErrorException;

use Yuansfer\ApiInterface;
use Yuansfer\Yuansfer;
use Yuansfer\Util\Sign;
use Yuansfer\Exception\HttpErrorException;
use Yuansfer\Exception\RequiredEmptyException;
use Yuansfer\Exception\YuansferException;
use Yuansfer\Exception\HttpClientException;

/**
 * Class AbstractApi
 *
 * @package Yuansfer\Api
 * @author  Feng Hao <flyinghail@msn.com>
 */
abstract class AbstractApi implements ApiInterface
{
    /**
     * @var Yuansfer
     */
    protected $yuansfer;

    /**
     * @var array
     */
    protected $params = array();

    protected static $required = array(
        'merchantNo',
        'storeNo',
    );

    public function __construct($yuansfer)
    {
        $this->yuansfer = $yuansfer;

        if (!Httpful::hasParserRegistered(Mime::JSON)) {
            Httpful::register(Mime::JSON, new JsonHandler(array('decode_as_array' => true)));
        }

        if (!Httpful::hasParserRegistered(Mime::FORM)) {
            Httpful::register(Mime::FORM, new FormHandler());
        }
    }

    /**
     * @param array|string $fields
     */
    protected function addRequired($fields)
    {
        static::$required = \array_unique(
            \array_merge(static::$required, (array) $fields)
        );
    }

    /**
     * @return string
     */
    abstract protected function getPath();

    /**
     * @return array
     */
    protected function getRequired()
    {
        return static::$required;
    }

    /**
     * @return string
     */
    protected function responseType()
    {
        return 'json';
    }

    /**
     * @inheritdoc
     */
    public function setMerchantNo($merchantNo)
    {
        $this->params['merchantNo'] = $merchantNo;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setStoreNo($storeNo)
    {
        $this->params['storeNo'] = $storeNo;

        return $this;
    }

    /**
     * @return string|array
     *
     * @throws YuansferException
     */
    public function send()
    {
        $path = $this->getPath();
        $url = $this->yuansfer->getUrl() . '/' . static::VERSION . '/' . $path;

        if (!isset($this->params['merchantNo'])) {
            $this->params['merchantNo'] = $this->yuansfer->getMerchantNo();
        }

        if (!isset($this->params['storeNo'])) {
            $this->params['storeNo'] = $this->yuansfer->getStoreNo();
        }

        foreach ($this->getRequired() as $k) {
            $found = false;
            if (\is_array($k)) {
                foreach ($k as $v) {
                    if (!isset($this->params[$v])) {
                        continue;
                    }

                    if (!$found && $this->params[$v] !== '') {
                        $found = true;
                    } else {
                        unset($this->params[$v]);
                    }
                }
            } else {
                $found = isset($this->params[$k]) && $this->params[$k] !== '';
            }

            if (!$found) {
                throw new RequiredEmptyException($path, $k);
            }
        }

        $params = Sign::append($this->params, $this->yuansfer->getApiToken());

        try {
            $response = Request::post($url, $params, 'form')
                ->expects($this->responseType())
                ->send();

            $code = $response->code;
            if ($code < 200 || $code >= 300) {
                throw new HttpErrorException($response);
            }

            return $response->body;
        } catch (ConnectionErrorException $e) {
            throw new HttpClientException($e->getMessage(), $e->getCode(), $e);
        }
    }
}