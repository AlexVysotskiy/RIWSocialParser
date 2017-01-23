<?php

namespace Parser\TwitterBundle;

use Parser\CoreBundle\PlatformParser;
use Parser\CoreBundle\Model\RequestResult;

/**
 * @author Alexander
 */
class PostParser extends PlatformParser
{

    /**
     * токен для подписи запросов
     * @var type 
     */
    protected $_accessToken = null;

    public function parse()
    {
        try {

            if (!$this->_accessToken) {
                $this->connect();
            }

            $url = 'https://api.twitter.com/1.1/search/tweets.json';

            $urlParams = array(
                'q' => $this->_settings['request'],
                'lang' => 'ru',
                'result_type' => 'recent',
                'count' => 100
            );

            if (isset($this->_settings['latest_post']) && $this->_settings['latest_post']) {
                $urlParams['since_id'] = $this->_settings['latest_post'];
            }

            if (isset($this->_settings['max_id']) && $this->_settings['max_id']) {

                $urlParams['max_id'] = $this->_settings['max_id'];
                $this->_settings['max_id'] = null;
            }

            $response = $this->makeRequest($url . '?' . http_build_query($urlParams), $this->getRequestOptions());
            return $response;
        } catch (\Exception $e) {

            throw $e;
        }
    }

    protected function getRequestOptions()
    {
        return array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->_accessToken
            ),
        );
    }

    /**
     * в каком формате возвращается ответ
     */
    protected function getResponseType()
    {
        return 'twitterPost';
    }

    /**
     * получаем access token к api twitter
     * алгоритм подключения https://dev.twitter.com/oauth/application-only
     */
    protected function connect()
    {
        // первый этап
        $consumerKey = rawurlencode($this->_settings['consumerKey']);
        $consumerSecret = rawurlencode($this->_settings['consumerSecret']);

        $bearerToken = base64_encode($consumerKey . ':' . $consumerSecret);

        // второй этап
        $options = array(
            CURLOPT_URL => 'https://api.twitter.com/oauth2/token',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query(array(
                'grant_type' => 'client_credentials'
            )),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . $bearerToken,
                'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
            ),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false
        );

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        $output = curl_exec($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error == '' && !preg_match('/error/', $output)) {

            $output = json_decode($output, true);
            $this->_accessToken = $output['access_token'];
        } else {
            throw new \Exception();
        }
    }

    /**
     * отключаем токен
     */
    protected function disconnect()
    {
        if ($this->_accessToken) {

            $options = array(
                CURLOPT_URL => 'https://api.twitter.com/oauth2/invalidate_token',
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => http_build_query(array(
                    'access_token' => $this->_accessToken
                )),
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER => 0,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
                ),
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false
            );

            $ch = curl_init();
            curl_setopt_array($ch, $options);

            curl_exec($ch);

            curl_close($ch);
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }

}
