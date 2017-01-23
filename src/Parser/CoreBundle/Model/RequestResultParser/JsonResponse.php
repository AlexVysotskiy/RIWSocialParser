<?php

namespace Parser\CoreBundle\Model\RequestResultParser;

use Parser\CoreBundle\Model\RequestResultParser;

/**
 * @author Alexander
 */
class JsonResponse extends RequestResultParser
{

    public function parse($content)
    {
        if (!is_string($content)) {
            return false;
        }

        return json_decode($content, true);
    }

}
