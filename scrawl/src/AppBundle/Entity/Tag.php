<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tag
 *
 * @ORM\Table(name="scrawl_tags")
 * @ORM\Entity
 */
class Tag
{

	 /**
     * @ORM\Id
     * @ORM\Column(name="id", type="string", length=60, unique=true)
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="username")
     * @ORM\JoinTable(name="added_tag")
     */
	 private $tagName;

     /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Photo", mappedBy="tags")
     */
     private $photos;
     

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->tagName;
    }

    /**
     * Set tagName
     *
     * @param string $tagName
     * @return Tag
     */
    public function setTagName($tagName)
    {
        $this->tagName = $tagName;

        return $this;
    }

    /**
     * Get tagName
     *
     * @return string 
     */
    public function getTagName()
    {
        return $this->tagName;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add photos
     *
     * @param \AppBundle\Entity\Tag $photos
     * @return Tag
     */
    public function addPhoto(\AppBundle\Entity\Tag $photos)
    {
        $this->photos[] = $photos;

        return $this;
    }

    /**
     * Remove photos
     *
     * @param \AppBundle\Entity\Tag $photos
     */
    public function removePhoto(\AppBundle\Entity\Tag $photos)
    {
        $this->photos->removeElement($photos);
    }

    /**
     * Get photos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPhotos()
    {
        return $this->photos;
    }
}
