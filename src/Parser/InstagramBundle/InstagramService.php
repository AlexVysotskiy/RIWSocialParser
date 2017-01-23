<?php

namespace Parser\InstagramBundle;

use Parser\CoreBundle\AbstractParserService;
use Parser\CoreBundle\Entity\Owner;
use Parser\CoreBundle\Entity\Post;
use Parser\CoreBundle\Entity\LatestPosts;

/**
 * @author Alexander
 */
class InstagramService extends AbstractParserService
{

    /**
     * парсер профиля
     * @var ProfileParser 
     */
    protected $_profileParser = null;

    /**
     * парсер страницы поиска
     * @var TagSearchParser 
     */
    protected $_searchTagParser = null;

    /**
     * парсер страницы поиска
     * @var ProfileInfoFromMediaParser 
     */
    protected $_profileInfoParser = null;

    /**
     * новые посты
     * @var type 
     */
    protected $_result = array();

    /**
     * новые авторы постов
     */
    protected $_owners = array();

    public function parse()
    {
        $this->_result = array();

        $this->_owners = array(
            'new' => array(),
            'info' => array(),
            'source' => array()
        );

        // забираем последние сохраненные посты
        $this->loadLatestPosts();

        // распарсили требуемые страницы в инсте
        $this->parseProfile();
        $this->parseSearchTag();

        // сохранили результат
        $this->storeResult();
    }

    protected function parseProfile()
    {
        $stored = array();
        $owners = array();

        if (isset($this->_settings['profiles'])) {

            $parser = $this->_profileParser;

            foreach ($this->_settings['profiles'] as $profile) {

                try {

                    $profilePosts = array();

                    $latestPost = $this->getLatestPost('profile', $profile);

                    $parser->addSetting('profile_id', $profile);

                    do {

                        /* @var $requestResult \Parser\CoreBundle\Model\RequestResult */
                        $requestResult = $parser->parse();
                        if ($requestResult->isSuccess() && @$requestResult->content['items']) {

                            if (!isset($stored[$profile])) {

                                $stored[$profile] = 1;

                                $owners[$requestResult->content['owner']['external_id']] = $requestResult->content['owner'];
                            }

                            $profilePosts += $requestResult->content['items'];

                            if (!isset($profilePosts[$latestPost])) {

                                $parser->addSetting('max_id', $requestResult->content['last']);
                            } else {
                                break;
                            }
                        } else {
                            break;
                        }
                    } while ($requestResult->isSuccess() && !$requestResult->isLast());

                    if (isset($profilePosts[$latestPost])) {

                        $slicePos = array_search($latestPost, array_keys($profilePosts));
                        $profilePosts = array_slice($profilePosts, 0, $slicePos, true);
                    }

                    // инфа по последним постам
                    reset($profilePosts);
                    if ($newLatest = key($profilePosts)) {
                        $this->_newLatest['profile_' . $profile] = $newLatest;
                    }

                    $this->_result += $profilePosts;
                } catch (\Exception $e) {

                    break;
                }
            }
        }


        $this->_owners['new'] = array_merge($this->_owners['new'], array_keys($owners));
        $this->_owners['info'] += $owners;
    }

    /**
     * парсим страницу
     */
    protected function parseSearchTag()
    {
        $owners = array();

        if (isset($this->_settings['tags'])) {

            $parser = $this->_searchTagParser;

            foreach ($this->_settings['tags'] as $tag) {

                try {

                    $posts = array();

                    $latestPost = $this->getLatestPost('tags', $tag);

                    $parser->addSetting('tag', $tag);
                    $parser->addSetting('max_id', null);

                    do {

                        /* @var $requestResult \Parser\CoreBundle\Model\RequestResult */
                        $requestResult = $parser->parse();

                        if ($requestResult->isSuccess() && @$requestResult->content['items']) {

                            $owners += $requestResult->content['owners'];

                            $posts += $requestResult->content['items'];

                            if (!isset($posts[$latestPost])) {
                                $parser->addSetting('max_id', $requestResult->content['last']);
                            } else {
                                break;
                            }
                        } else {

                            break;
                        }
                    } while ($requestResult->isSuccess() && !$requestResult->isLast());

                    if (isset($posts[$latestPost])) {

                        $slicePos = array_search($latestPost, array_keys($posts));
                        $posts = array_slice($posts, 0, $slicePos, true);
                    }

                    // инфа по последним постам
                    reset($posts);
                    if ($newLatest = key($posts)) {
                        $this->_newLatest['tags_' . $tag] = $newLatest;
                    }

                    $this->_result += $posts;
                } catch (\Exception $e) {

                    break;
                }
            }
        }

        $this->_owners['new'] = array_merge($this->_owners['new'], array_keys($owners));
        $this->_owners['source'] += $owners;
    }

    /**
     * парсим инфу пользователя
     * @param type $owners
     */
    protected function parseOwnersInfo($owners)
    {
        $result = array();

        if ($owners) {

            $this->_profileInfoParser->addSetting('search_owners', $owners);
            $result = $this->_profileInfoParser->parse()->getContent();
        }

        return $result;
    }

    protected function storeResult()
    {
        /* дикая дичь, сори */
        if ($this->_result) {
            
            $pl = $this->getCurrentPlatform();
            

            // сначала сохранить инфу об авторах
            // затем выбрать инфу обо всех авторах из постов
            // добавить посты
            // обновить инфу по последним полученным постам
            // сохранили инфу по новым авторам, достали старых

            /* @var $ownerRepo \Parser\CoreBundle\Repository\OwnerRepository */
            $ownerRepo = $this->_entityManager->getRepository(Owner::ALIAS);

            // на основе id авторов постов в инст определяем, кто уже у нас есть в базе
            $this->_owners['new'] = array_unique($this->_owners['new']);

            // смотрим новых, определяем, есть ли уже собранная инфа. если нет, 
            // делаем запросы к последним известным постам этих людей и забриаем инфу по профилю оттуда
            $newOwnersIds = $ownerRepo->filterExisted($this->_owners['new'], $pl);
            $requireInfo = array_diff($newOwnersIds, array_keys($this->_owners['info']));
            $requireInfo = array_intersect_key($this->_owners['source'], array_fill_keys($requireInfo, 1));

            $ownersInfoToStore = array_intersect_key($this->_owners['info'], array_fill_keys($newOwnersIds, 1)) + $this->parseOwnersInfo($requireInfo);

            $postOwners = array();

            if ($ownersInfoToStore) {
                $postOwners += $ownerRepo->storeOwnersInfo($ownersInfoToStore, $pl);
            }

            if ($existed = array_diff($this->_owners['new'], $newOwnersIds)) {

                $postOwners += $ownerRepo->findByExternalId($existed, $pl);
            }

            // начали обрабатывать посты
            
            foreach ($this->_result as $postInfo) {

                if (isset($postOwners[$postInfo['owner']])) {

                    $post = new Post();
                    $post->fromArray($postInfo);
                    $post->setOwner($postOwners[$postInfo['owner']]);

                    $this->_entityManager->persist($post);
                }
            }

            $this->_entityManager->flush();

            $this->updateLatestPosts();
        }
    }

    public function setProfileParser($parser)
    {
        $this->_profileParser = $parser;
    }

    public function setSearchTagParser($parser)
    {
        $this->_searchTagParser = $parser;
    }

    public function setProfileInfoParser($parser)
    {
        $this->_profileInfoParser = $parser;
    }
    
     protected function getCurrentPlatform()
    {
        return Post::POST_PLATFORM_INST;
    }

}
