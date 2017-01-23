<?php

namespace Parser\InstagramBundle\Model\RequestResultParser;

use Parser\CoreBundle\Model\RequestResultParser\JsonResponse;

/**
 * парсим результаты со поиска по тегам в инсте
 *
 * @author Alexander
 */
class TagSearch extends JsonResponse
{

    public function parse($content)
    {
        $jsonResult = parent::parse($content);

        if ($jsonResult) {

            if (isset($jsonResult['tag']['media']['nodes'])) {

                $result = array(
                    'owners' => array(),
                    'items' => array(),
                    'last' => null
                );

                foreach ($jsonResult['tag']['media']['nodes'] as $itemInfo) {

                    $owner = $itemInfo['owner']['id'];
                    if (!isset($result['owners'][$owner])) {

                        $result['owners'][$owner] = $itemInfo['code'];
                    }
                    
                    $item = array(
                        'external_id' => $itemInfo['id'],
                        'text' => base64_encode(trim(mb_convert_encoding(htmlentities(@$itemInfo['caption']), 'UTF-8'))),
                        'type' => $itemInfo['is_video'] ? 'video' : 'image',
                        'date' => $itemInfo['date'],
                        'code' => $itemInfo['code'],
                        'link' => 'https://www.instagram.com/p/' . $itemInfo['code'] . '/',
                        'thumbnail' => $itemInfo['thumbnail_src'],
                        'img' => $itemInfo['display_src'],
                        'owner' => $owner,
                        'platform' => \Parser\CoreBundle\Entity\Post::POST_PLATFORM_INST,
                        'source' => 'tags_' . $jsonResult['tag']['name']
                    );

                    $result['items'][$item['external_id']] = $item;
                }

                $result['last'] = $jsonResult['tag']['media']['page_info']['end_cursor'];

                $this->_last = isset($jsonResult['tag']['media']['page_info']['has_next_page']) ? !$jsonResult['tag']['media']['page_info']['has_next_page'] : true;

                return $result;
            }
        }

        $this->_last = true;

        return false;
    }

}
