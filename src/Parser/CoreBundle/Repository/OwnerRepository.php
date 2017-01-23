<?php

namespace Parser\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Parser\CoreBundle\Entity\Owner;

/**
 * Description of OwnerRepository
 *
 * @author Alexander
 */
class OwnerRepository extends EntityRepository
{

    public function findByExternalId($ids, $platformType)
    {
        $result = $this->findBy(array(
            'externalId' => $ids,
            'platform' => $platformType
        ));

        $order = array();
        /* @var $owner Owner */
        foreach ($result as $owner) {
            $order[] = $owner->getExternalId();
        }

        return array_combine($order, $result);
    }

    public function filterExisted($ownerExternalIds, $platformType)
    {
        $qb = $this->createQueryBuilder(Owner::ALIAS);
        $qb->select('owner.externalId')
                ->from(Owner::ALIAS, 'owner')
                ->where('owner.platform = :platform and owner.externalId IN(:ids)')
                ->setParameter('platform', $platformType)
                ->setParameter('ids', $ownerExternalIds);

        $result = $qb->getQuery()->getResult();
        $existed = array();

        foreach ($result as $owner) {
            $existed[] = $owner['externalId'];
        }

        return array_diff($ownerExternalIds, $existed);
    }

    public function storeOwnersInfo($ownersInfo, $platformType)
    {
        $result = array();

        if ($ownersInfo) {

            foreach ($ownersInfo as $info) {

                $owner = new Owner();
                $owner->fromArray($info);

                $this->_em->persist($owner);

                $result[$owner->getExternalId()] = $owner;
            }

            $this->_em->flush();
        }

        return $result;
    }

}
