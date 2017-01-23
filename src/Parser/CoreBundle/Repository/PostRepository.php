<?php

namespace Parser\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Parser\CoreBundle\Entity\Owner;
use Parser\CoreBundle\Entity\Post;

/**
 * Description of OwnerRepository
 *
 * @author Alexander
 */
class PostRepository extends EntityRepository
{

    // SELECT p.* FROM social_posts as p FORCE INDEX (date_idx) LEFT JOIN posts_owners as o on p.owner_id = o.id ORDER BY p.date desc
    public function getPostList($page = 0, $perPage = 30)
    {
        $entityName = Post::ALIAS;
        $ownerEntity = Owner::ALIAS;

        $qb = $this->_em->createQuery("SELECT p.code as id, p.date, p.text, p.platform as type"
                        . ", p.link, p.img as photo, o.login as username, o.img as avatar,"
                        . " o.link as profileLink FROM $entityName p left join $ownerEntity"
                        . " o WITH o.id = p.owner ORDER BY p.date DESC")
                ->setFirstResult($page * $perPage)
                ->setMaxResults($perPage);

        $result = $qb->getResult();

        array_walk($result, function(&$item)
        {
            $item['date'] = $item['date']->getTimestamp();
            $item['text'] = base64_decode($item['text']);
        });

        return $result;
    }

}
