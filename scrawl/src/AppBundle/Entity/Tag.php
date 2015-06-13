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
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
	 private $id;

	 /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
	 private $tagName;

     /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", mappedBy="tags")
     */
     private $photos;
     




    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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
}
