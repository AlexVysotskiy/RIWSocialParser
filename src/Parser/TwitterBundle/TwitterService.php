<?php

namespace Parser\TwitterBundle;

use Parser\CoreBundle\AbstractParserService;
use Parser\CoreBundle\Entity\Owner;
use Parser\CoreBundle\Entity\Post;
use Parser\CoreBundle\Entity\LatestPosts;

/**
 * @author Alexander
 */
class TwitterService extends AbstractParserService
{

    /**
     * парсер профиля
     * @var \Parser\TwitterBundle\PostParser 
     */
    protected $_parser = null;

    public function parse()
    {
        // забираем последние сохраненные посты
        $this->loadLatestPosts();

        $parser = $this->_parser;

        $owners = array();
        $posts = array();

        foreach ($this->_settings['search'] as $searchRequest) {

            $parser->addSetting('request', $searchRequest);

            $currentPosts = array();

            try {

                $latestPost = $this->getLatestPost('search', $searchRequest);
                $parser->addSetting('latest_post', $latestPost);

                do {

                    /* @var $requestResult \Parser\CoreBundle\Model\RequestResult */
                    $requestResult = $parser->parse();

                    if ($requestResult->isSuccess() && @$requestResult->content['items']) {

                        $owners += $requestResult->content['owners'];

                        if (array_diff_key($requestResult->content['items'], $currentPosts)) {
                            
                            $currentPosts += $requestResult->content['items'];

                            if (!isset($currentPosts[$latestPost])) {

                                $parser->addSetting('max_id', $requestResult->content['last']);
                            } else {
                                break;
                            }
                        } else {
                            break;
                        }
                    }
                } while ($requestResult->isSuccess() && !$requestResult->isLast());
            } catch (\Exception $e) {
                
            }

            if (isset($currentPosts[$latestPost])) {

                $slicePos = array_search($latestPost, array_keys($currentPosts));
                $currentPosts = array_slice($currentPosts, 0, $slicePos, true);
            }

            // инфа по последним постам
            reset($currentPosts);
            if ($newLatest = key($currentPosts)) {
                $this->_newLatest['search_' . $searchRequest] = $newLatest;
            }

            $posts = array_merge($posts, $currentPosts);
        }

        // распарсили требуемые страницы в твиттере
        // сохранили результат
        $this->storeResult($owners, $posts);
    }

    protected function storeResult($owners, $posts)
    {
        /* дикая дичь, сори */
        $pl = $this->getCurrentPlatform();

        // сначала сохранить инфу об авторах
        // затем выбрать инфу обо всех авторах из постов
        // добавить посты
        // обновить инфу по последним полученным постам
        // сохранили инфу по новым авторам, достали старых

        /* @var $ownerRepo \Parser\CoreBundle\Repository\OwnerRepository */
        $ownerRepo = $this->_entityManager->getRepository(Owner::ALIAS);

        // смотрим новых, определяем, есть ли уже собранная инфа. если нет, 
        // делаем запросы к последним известным постам этих людей и забриаем инфу по профилю оттуда
        $newOwnersIds = $ownerRepo->filterExisted(array_keys($owners), $pl);

        $postOwners = array();

        if ($newOwnersIds) {
            $postOwners += $ownerRepo->storeOwnersInfo(array_intersect_key($owners, array_fill_keys($newOwnersIds, 1)), $pl);
        }

        if ($existed = array_diff(array_keys($owners), $newOwnersIds)) {

            $postOwners += $ownerRepo->findByExternalId($existed, $pl);
        }

        // начали обрабатывать посты
        /* @var $ownerRepo \Parser\CoreBundle\Repository\OwnerRepository */
        foreach ($posts as $postInfo) {

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

    public function setParser($parser)
    {
        $this->_parser = $parser;
    }

    protected function getCurrentPlatform()
    {
        return Post::POST_PLATFORM_TWITTER;
    }

}
