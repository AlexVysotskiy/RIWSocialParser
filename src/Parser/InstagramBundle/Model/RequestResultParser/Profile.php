<?php

namespace Parser\InstagramBundle\Model\RequestResultParser;

use Parser\CoreBundle\Model\RequestResultParser\JsonResponse;

/**
 * парсим результаты со страницы профиля в инсте
 *
 * @author Alexander
 */
class Profile extends JsonResponse
{

    public function parse($content)
    {
        $jsonResult = parent::parse($content);

        if ($jsonResult) {

            if (isset($jsonResult['items'])) {

                $result = array(
                    'owner' => array(),
                    'items' => array(),
                    'last' => null
                );

                foreach ($jsonResult['items'] as $itemInfo) {

                    if (!$result['owner']) {

                        $result['owner'] = array(
                            'external_id' => $itemInfo['user']['id'],
                            'login' => $itemInfo['user']['username'],
                            'img' => $itemInfo['user']['profile_picture'],
                            'name' => base64_encode(trim(mb_convert_encoding(htmlentities($itemInfo['user']['full_name']), 'UTF-8'))),
                            'link' => 'https://www.instagram.com/' . $itemInfo['user']['username'] . '/',
                            'platform' => \Parser\CoreBundle\Entity\Post::POST_PLATFORM_INST,
                        );
                    }

                    $ownerId = $result['owner']['external_id'];

                    $item = array(
                        'external_id' => str_replace('_' . $ownerId, '', $itemInfo['id']),
                        'text' => base64_encode(trim(htmlentities(@$itemInfo['caption']['text']))),
                        'type' => $itemInfo['type'],
                        'date' => $itemInfo['created_time'],
                        'code' => $itemInfo['code'],
                        'link' => $itemInfo['link'],
                        'thumbnail' => $itemInfo['images']['thumbnail']['url'],
                        'img' => $itemInfo['images']['standard_resolution']['url'],
                        'owner' => $itemInfo['user']['id'],
                        'platform' => \Parser\CoreBundle\Entity\Post::POST_PLATFORM_INST,
                        'source' => 'profile_' . $result['owner']['login']
                    );

                    $result['items'][$item['external_id']] = $item;

                    $result['last'] = $itemInfo['id'];
                }

                $this->_last = isset($jsonResult['more_available']) ? !$jsonResult['more_available'] : true;

                return $result;
            }
        }

        $this->_last = true;

        return false;
    }

}
