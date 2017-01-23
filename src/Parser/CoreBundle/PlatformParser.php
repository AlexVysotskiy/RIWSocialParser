<?php

namespace Parser\CoreBundle;

use Parser\CoreBundle\Model\RequestResult;
use Parser\CoreBundle\Model\RequestResultParser\Factory;
use Parser\CoreBundle\Interfaces\Parser;

/**
 * Парсим страницу платформы / делаем запрос к апи
 * @author Alexander
 */
abstract class PlatformParser implements Parser
{

    /**
     * настройки парсера
     * @var type 
     */
    protected $_settings = array();

    /**
     *
     * @var Factory 
     */
    protected $_requestParserFactory = null;

    public function setSettings($settings)
    {
        $this->_settings = $settings;
    }

    public function addSetting($setting, $value)
    {
        $this->_settings[$setting] = $value;
    }

    /**
     * 
     * @param string $url
     * @param array $extraOptions
     * @return RequestResult
     */
    protected function makeRequest($url, $extraOptions = null)
    {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_TIMEOUT => 10,
        );

        if ($extraOptions && is_array($extraOptions)) {
            $options += $extraOptions;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        $error = curl_error($ch);

        $output = curl_exec($ch);
        curl_close($ch);
        
        $responseResult = new RequestResult();

        if ($responseType = $this->getResponseType()) {

            $responseResult->setContentParser($this->_requestParserFactory->getRequestParser($responseType));
        }

        if ($error == '') {

            $responseResult->setIsSuccess(true);
            $responseResult->setContent($output);
        } else {

            $responseResult->setIsSuccess(true);
            $responseResult->setError($error);
        }

        return $responseResult;
    }

    public function setRequestParserFactory($factory)
    {
        $this->_requestParserFactory = $factory;
    }

    /**
     * в каком формате возвращается ответ
     */
    protected function getResponseType()
    {
        return null;
    }

    protected function getRequestOptions()
    {
        return array();
    }

}
