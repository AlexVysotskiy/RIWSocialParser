<?php

namespace Parser\InstagramBundle\Model\RequestResultParser;

use Parser\CoreBundle\Model\RequestResultParser\JsonResponse;

/**
 * парсим результаты со страницы профиля в инсте
 *
 * @author Alexander
 */
class ProfileFromMedia extends JsonResponse
{

    public function parse($content)
    {
        $result = array();

        foreach ($content as $mediaInfo) {

            if (($mediaInfo = json_decode($mediaInfo, true)) && isset($mediaInfo['media']['owner'])) {

                $owner = $mediaInfo['media']['owner'];

                $result[$owner['id']] = array(
                    'external_id' => $owner['id'],
                    'login' => $owner['username'],
                    'img' => $owner['profile_pic_url'],
                    'name' => base64_encode(trim(mb_convert_encoding(htmlentities($owner['full_name']), 'UTF-8'))),
                    'link' => 'https://www.instagram.com/' . $owner['username'] . '/',
                    'platform' => \Parser\CoreBundle\Entity\Post::POST_PLATFORM_INST
                );
            }
        }

        return $result;
    }

}
