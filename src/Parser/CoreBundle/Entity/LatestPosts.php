<?php

namespace Parser\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Parser\CoreBundle\Repository\LatestPostsRepository")
 * @ORM\Table(name="latest_posts")
 *
 * @author Alexander
 */
class LatestPosts
{

    const ALIAS = __CLASS__;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * id поста в платформе
     * @ORM\Column(name="external_id", type="string", length=50)
     * @var type 
     */
    protected $externalId;

    /**
     * платформа
     * @ORM\Column(name="platform", type="string", length=20)
     * @var type 
     */
    protected $platform;

    /**
     * откуда был получен этот пост, из профиля, со страницы поиска и пр
     * @ORM\Column(name="source", type="string", length=20)
     * @var type 
     */
    protected $source;

    public function getExternalId()
    {
        return $this->externalId;
    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

}
