<?php

namespace Parser\CoreBundle;

use Parser\CoreBundle\Interfaces\Parser;
use Parser\CoreBundle\Entity\Owner;
use Parser\CoreBundle\Entity\Post;
use Parser\CoreBundle\Entity\LatestPosts;

/**
 * @author Alexander
 */
abstract class AbstractParserService implements Parser
{

    /**
     *
     * @var \Doctrine\ORM\EntityManager 
     */
    protected $_entityManager = null;

    /**
     * настройки парсинга
     * @var type 
     */
    protected $_settings = array();

    /**
     * новые последние посты
     * @var type 
     */
    protected $_newLatest = array();

    /**
     * текущие последние посты
     * @var type 
     */
    protected $_latest = array();

    public function __construct($entityManager)
    {
        $this->_entityManager = $entityManager;
    }

    public function setSettings($settings)
    {
        $this->_settings = $settings;
    }

    protected function loadLatestPosts()
    {
        /* @TODO сделать ориентацию не только на посты но и на время их создания */

        /* @var $repo \Parser\CoreBundle\Repository\LatestPostsRepository */
        $repo = $this->_entityManager->getRepository(LatestPosts::ALIAS);

        $pl = $this->getCurrentPlatform();
        $this->_latest = $repo->getLatestPostForPlatform($pl);
    }

    protected function updateLatestPosts()
    {
        $pl = $this->getCurrentPlatform();
        
        foreach ($this->_newLatest as $source => $id) {

            if (isset($this->_latest[$source])) {

                $this->_latest[$source]->setExternalId($id);
            } else {

                $entity = new LatestPosts();
                $entity->setExternalId($id);
                $entity->setPlatform($pl);
                $entity->setSource($source);

                $this->_entityManager->persist($entity);
            }
        }

        $this->_entityManager->flush();
    }

    protected function getLatestPost($searchType, $id)
    {
        return isset($this->_latest[$searchType . '_' . $id]) ? $this->_latest[$searchType . '_' . $id]->getExternalId() : null;
    }
    
    protected function getCurrentPlatform()
    {
        return null;
    }

}
