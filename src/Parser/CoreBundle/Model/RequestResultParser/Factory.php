<?php

namespace Parser\CoreBundle\Model\RequestResultParser;

/**
 * Description of Factory
 *
 * @author Alexander
 */
class Factory
{

    /**
     * доп типы парсеров, задаем в конфиге
     * @var array 
     */
    protected $_parsersList = array();

    /* @TODO make this factory as service and add different types from config */

    public function getRequestParser($type)
    {
        if (isset($this->_parsersList[$type]) && class_exists($this->_parsersList[$type])) {

            $className = $this->_parsersList[$type];
            return new $className();
        }

        switch ($type) {
            case 'json':
                return new JsonResponse();
            case 'jsonProfile':
                return new Parser\InstagramBundle\Model\RequestResultParser\JsonProfile();
            default:
                return new PlainTextResponse();
        }
    }

    public function setParsersList($parsersList)
    {
        $this->_parsersList = $parsersList;
    }

}
