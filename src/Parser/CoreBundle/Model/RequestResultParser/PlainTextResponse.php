<?php

namespace Parser\CoreBundle\Model\RequestResultParser;

use Parser\CoreBundle\Model\RequestResultParser;

/**
 * @author Alexander
 */
class PlainTextResponse extends RequestResultParser
{

    public function parse($content)
    {
        return is_string($content) ? $content : false;
    }

}
