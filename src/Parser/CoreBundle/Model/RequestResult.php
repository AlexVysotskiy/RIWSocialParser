<?php

namespace Parser\CoreBundle\Model;

use Parser\CoreBundle\Model\RequestResultParser;
use Parser\CoreBundle\Model\RequestResultParser\SimpleResponse;

/**
 * Description of RequestResult
 *
 * @author Alexander
 */
class RequestResult
{

    /**
     * результат запроса
     * @var type 
     */
    public $content = null;

    /**
     * успешен ли запрос
     * @var type 
     */
    protected $_success = false;

    /**
     * по возможности проставляем ошибку
     * @var type 
     */
    protected $_error = null;

    /**
     * является ли данный результат последним
     * @var type 
     */
    protected $_last = false;

    /**
     * парсим содержимое ответа
     * @var type 
     */
    protected $_parser = null;

    public function __construct()
    {
        $this->_parser = new SimpleResponse();
    }

    public function setIsSuccess($isSuccess)
    {
        $this->_success = $isSuccess;
    }

    public function isSuccess()
    {
        return $this->_success;
    }

    public function setIsLast($isLast)
    {
        $this->_last = $isLast;
    }

    public function isLast()
    {
        return $this->_last;
    }

    public function setContent($content)
    {
        $this->content = $this->_parser->parse($content);
        $this->setIsLast($this->_parser->isLast());

        if ($this->content === false || $this->content === null) {

            $this->_success = false;
            $this->_error = 'Empty content';
        }
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setError($error)
    {
        $this->_error = $error;
    }

    public function getError()
    {
        return $this->_error;
    }

    public function setContentParser(RequestResultParser $parser)
    {
        $this->_parser = $parser;
    }

}
