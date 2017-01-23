<?php

namespace Parser\InstagramBundle;

use Parser\CoreBundle\PlatformParser;
use Parser\CoreBundle\Model\RequestResult;

/**
 * Для авторов, которых мы не нашли в БД, делаем запросы по последним известным страницам с контентом
 * @author Alexander
 */
class ProfileInfoFromMediaParser extends PlatformParser
{

    public function parse()
    {
        if ($this->_settings['search_owners']) {

            $baseUrl = $this->getBaseUrl();

            $urls = array();
            foreach ($this->_settings['search_owners'] as $owner => $mediaCode) {
                $urls[] = $baseUrl . $mediaCode . '/?__a=1';
            }

            $currentResponse = $this->makeRequest($urls, $this->getRequestOptions());

            return $currentResponse;
        }
    }

    /**
     * 
     * @param string $url
     * @param array $extraOptions
     * @return RequestResult
     */
    protected function makeRequest($url, $extraOptions = null)
    {

        if (!is_array($url)) {
            $url = array($url);
        }

        $options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => 10,
        );

        if ($extraOptions && is_array($extraOptions)) {
            $options += $extraOptions;
        }

        // сохраняем вывод инфы
        $output = array();

        // делаем так, чтобы много параллельных запросов не отсылать
        $counter = 0;
        $maxRequests = 100;
        while ($urls = array_slice($url, $counter * $maxRequests, $maxRequests)) {

            $master = curl_multi_init();

            foreach ($urls as $mediaUrl) {

                $ch = curl_init();
                $options[CURLOPT_URL] = $mediaUrl;
                curl_setopt_array($ch, $options);
                curl_multi_add_handle($master, $ch);
            }

            // СТАРТУЕМ! параллельные запросы для генерации
            do {

                while (($execrun = curl_multi_exec($master, $running)) == CURLM_CALL_MULTI_PERFORM) {
                    
                }

                if ($execrun != CURLM_OK) {
                    break;
                }

                // a request was just completed -- find out which one
                while ($done = curl_multi_info_read($master)) {

                    $info = curl_getinfo($done['handle']);

                    if ($info['http_code'] == 200) {

                        $output[] = curl_multi_getcontent($done['handle']);

                        /*
                         * в оригинале вообще непонятная хуйня, хотя я скорее всего туп, чтобы понять это
                         * $options[CURLOPT_URL] = $urls[$i++];  // increment i FROM http://www.onlineaspect.com/2009/01/26/how-to-use-curl_multi-without-blocking/
                         */
                        $options[CURLOPT_URL] = NULL;

                        // start a new request (it's important to do this before removing the old one)
                        $ch = curl_init();
                        curl_setopt_array($ch, $options);
                        curl_multi_add_handle($master, $ch);

                        // remove the curl handle that just completed
                        curl_multi_remove_handle($master, $done['handle']);
                    } else {
                        // request failed.  add error handling.
                    }
                }
            } while ($running);

            curl_multi_close($master);

            $counter++;
        }

        $responseResult = new RequestResult();

        if ($responseType = $this->getResponseType()) {

            $responseResult->setContentParser($this->_requestParserFactory->getRequestParser($responseType));
        }

        $responseResult->setIsSuccess(true);
        $responseResult->setContent($output);

        return $responseResult;
    }

    protected function getRequestOptions()
    {
        return array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_SSL_VERIFYPEER => false
        );
    }

    protected function getBaseUrl()
    {
        return 'https://www.instagram.com/p/';
    }

    protected function getResponseType()
    {
        return 'instProfileMedia';
    }

}
