<?php

namespace Parser\InstagramBundle;

use Parser\CoreBundle\PlatformParser;

/**
 * Парсер для профиля в инстаграме
 * @author Alexander
 */
class ProfileParser extends PlatformParser
{

    public function parse()
    {
        $baseUrl = $this->getBaseUrl();

        if (isset($this->_settings['max_id']) && $this->_settings['max_id']) {
            
            $baseUrl .= '?max_id=' . $this->_settings['max_id'];
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
        return 'https://www.instagram.com/' . $this->_settings['profile_id'] . '/media/';
    }

    protected function getResponseType()
    {
        return 'instProfile';
    }

}
