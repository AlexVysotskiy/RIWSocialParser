<?php

namespace Parser\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Parser\CoreBundle\Entity\LatestPosts;

/**
 * Description of OwnerRepository
 *
 * @author Alexander
 */
class LatestPostsRepository extends EntityRepository
{

    public function getLatestPostForPlatform($platform)
    {
        $result = $this->findBy(array(
            'platform' => $platform
        ));

        $order = array();
        /* @var $post LatestPosts */
        foreach ($result as $post) {
            $order[] = $post->getSource();
        }

        return array_combine($order, $result);
    }

}
