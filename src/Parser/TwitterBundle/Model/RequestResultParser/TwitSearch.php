<?php

namespace Parser\TwitterBundle\Model\RequestResultParser;

use Parser\CoreBundle\Model\RequestResultParser\JsonResponse;

/**
 * парсим страницу поиска твиттера
 *
 * @author Alexander
 */
class TwitSearch extends JsonResponse
{

    public function parse($content)
    {
        $jsonResult = parent::parse($content);

        if (isset($jsonResult['statuses'])) {

            $result = array(
                'owners' => array(),
                'items' => array(),
                'last' => null
            );

            $sourceType = 'search_' . urldecode($jsonResult['search_metadata']['query']);

            foreach ($jsonResult['statuses'] as $itemInfo) {

                $userInfo = $itemInfo['user'];
                $ownerId = intval($userInfo['id_str']);

                if (!isset($result['owners'][$ownerId])) {

                    $result['owners'][$ownerId] = array(
                        'external_id' => $ownerId,
                        'login' => $userInfo['screen_name'],
                        'img' => $userInfo['profile_image_url_https'],
                        'name' => base64_encode(trim(htmlentities($userInfo['name']), 'UTF-8')),
                        'link' => 'https://twitter.com/' . $userInfo['screen_name'],
                        'platform' => \Parser\CoreBundle\Entity\Post::POST_PLATFORM_TWITTER,
                    );
                }

                $item = array(
                    'external_id' => $itemInfo['id_str'],
                    'text' => base64_encode(trim(htmlentities(@$itemInfo['text']))),
//                    'text' => trim(htmlentities(@$itemInfo['text'])),
                    'type' => 'text',
                    'date' => strtotime($itemInfo['created_at']),
                    'code' => $itemInfo['id_str'],
                    'link' => 'https://twitter.com/statuses/' . $itemInfo['id_str'],
                    'thumbnail' => null,
                    'img' => null,
                    'owner' => $ownerId,
                    'platform' => \Parser\CoreBundle\Entity\Post::POST_PLATFORM_TWITTER,
                    'source' => $sourceType
                );

                $result['items'][$item['external_id']] = $item;
                $result['last'] = $item['external_id'];
            }

            $this->_last = count($result['items']) == 0;

            return $result;
        }

        return false;
    }

}
