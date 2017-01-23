<?php

namespace Parser\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Parser\CoreBundle\Repository\OwnerRepository")
 * @ORM\Table(name="posts_owners",indexes={@ORM\Index(name="owner_idx", columns={"external_id", "platform"})})
 *
 * @author Alexander
 */
class Owner
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
     * @ORM\Column(name="external_id", type="string", nullable=true, length=50)
     * @var type 
     */
    protected $externalId;

    /**
     * login, если есть
     * @ORM\Column(name="login", type="string", nullable=true)
     * @var type 
     */
    protected $login;

    /**
     * название поста, если есть
     * @ORM\Column(name="name", type="string", nullable=true)
     * @var type 
     */
    protected $name;

    /**
     * ссылка на пост
     * @ORM\Column(name="link", type="string")
     * @var type 
     */
    protected $link;

    /**
     * главное изображение поста, если есть
     * @ORM\Column(name="img", type="string", nullable=true)
     * @var type 
     */
    protected $img;

    /**
     * платформа
     * @ORM\Column(name="platform", type="string", length=20)
     * @var type 
     */
    protected $platform;

    public function getId()
    {
        return $this->id;
    }

    public function getExternalId()
    {
        return $this->externalId;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getImg()
    {
        return $this->img;
    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    public function setImg($img)
    {
        $this->img = $img;
        return $this;
    }

    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }

    public function fromArray(array $params)
    {
        $this->externalId = $params['external_id'];
        $this->login = @$params['login'];
        $this->img = @$params['img'];
        $this->name = @$params['name'];
        $this->link = @$params['link'];
        $this->platform = $params['platform'];
    }

}
