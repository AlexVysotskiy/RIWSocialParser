<?php

namespace Parser\CoreBundle\Model;

/**
 * @author Alexander
 */
abstract class RequestResultParser
{

    /**
     *
     * @var type 
     */
    protected $_last = false;
    
    abstract public function parse($content);
    
    
    public function isLast()
    {
        return $this->_last;
    }
}
