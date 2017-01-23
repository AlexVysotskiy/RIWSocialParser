<?php

namespace Parser\InstagramBundle;

use Parser\CoreBundle\PlatformParser;

/**
 * Парсер для страницы поиска по тегу в инстаграме
 * @author Alexander
 */
class TagSearchParser extends PlatformParser
{

    public function parse()
    {
        $baseUrl = $this->getBaseUrl();
        
        if (isset($this->_settings['max_id']) && $this->_settings['max_id']) {

            $baseUrl .= '&max_id=' . $this->_settings['max_id'];
            $this->_settings['max_id'] = null;
        }
        
        $currentResponse = $this->makeRequest($baseUrl, $this->getRequestOptions());
        return $currentResponse;
    }

    protected function getRequestOptions()
    {
        return array(
            CURLOPT_SSL_VERIFYPEER => false
        );
    }

    protected function getBaseUrl()
    {
        return 'https://www.instagram.com/explore/tags/' . $this->_settings['tag'] . '/?__a=1';
    }

    protected function getResponseType()
    {
        return 'instTagSearch';
    }

}
