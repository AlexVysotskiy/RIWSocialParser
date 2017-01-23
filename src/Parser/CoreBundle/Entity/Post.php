<?php

namespace Parser\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Parser\CoreBundle\Repository\PostRepository")
 * @ORM\Table(name="social_posts",indexes={@ORM\Index(name="date_idx", columns={"date"})})
 *
 * @author Alexander
 */
class Post
{

    const ALIAS = __CLASS__;
    const POST_PLATFORM_INST = 'in';
    const POST_PLATFORM_FB = 'fb';
    const POST_PLATFORM_TWITTER = 'tw';

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
     * код поста (доп к externalId)
     * @ORM\Column(name="code", type="string", nullable=true, length=50)
     * @var type 
     */
    protected $code;

    /**
     * название поста, если есть
     * @ORM\Column(name="name", type="string", nullable=true)
     * @var type 
     */
    protected $name;

    /**
     * содержимое поста
     * @ORM\Column(name="text", type="text", nullable=true)
     * @var type 
     */
    protected $text;

    /**
     * тип поста - фото, видео, текст и т.д.
     * @ORM\Column(name="type", type="string", length=10, nullable=true)
     * @var type 
     */
    protected $type;

    /**
     * время создания поста
     * @ORM\Column(name="date", type="datetime")
     * @var \DateTime 
     */
    protected $date;

    /**
     * ссылка на пост
     * @ORM\Column(name="link", type="string")
     * @var type 
     */
    protected $link;

    /**
     * превью поста если есть
     * @ORM\Column(name="thumbnail", type="string", nullable=true)
     * @var type 
     */
    protected $thumbnail;

    /**
     * главное изображение поста, если есть
     * @ORM\Column(name="img", type="string", nullable=true)
     * @var type 
     */
    protected $img;

    /**
     * автор поста
     * 
     * @ORM\ManyToOne(targetEntity="\Parser\CoreBundle\Entity\Owner")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     * @var type 
     */
    protected $owner;

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

    public function __construct()
    {
        $this->date = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getExternalId()
    {
        return $this->externalId;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function getImg()
    {
        return $this->img;
    }

    public function getOwner()
    {
        return $this->owner;
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

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    public function setImg($img)
    {
        $this->img = $img;
        return $this;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }

    public function fromArray(array $params)
    {
        $this->code = @$params['code'];
        $this->externalId = @$params['external_id'];
        $this->img = @$params['img'];
        $this->thumbnail = @$params['thumbnail'];
        $this->link = $params['link'];
        $this->type = @$params['type'];
        $this->text = $params['text'];
        $this->name = @$params['name'];
        $this->platform = $params['platform'];
        $this->source = $params['source'];

        if (isset($params['date'])) {

            $this->date->setTimestamp($params['date']);
        }
    }

}
