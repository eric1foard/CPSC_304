<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Mapping\ClassMetadata;


/**
 * Photo
 *
 * @ORM\Table(name="scrawl_photos")
 * @ORM\Entity
 */
class Photo
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="string", length=80, nullable=true)
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="username")
     * @ORM\JoinTable(name="seen_in_person")
     */
    private $path;

    /**
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="photos")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=60, unique=false)
     */
    private $uploadDate;


    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $longitude;

    /** @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", inversedBy="photos")
    *   @ORM\JoinTable(name="has_tag")
    */
    public $tags;



    /**
     * Get id
     *
     * @return string $path 
     */
    public function getId()
    {
        return $this->path;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Photo
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set uploadDate
     *
     * @param string $uploadDate
     * @return Photo
     */
    public function setUploadDate($uploadDate)
    {
        $this->uploadDate = $uploadDate;

        return $this;
    }

    /**
     * Get uploadDate
     *
     * @return string 
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Photo
     */
    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

#################################
##        Upload Helpers       ##
#################################
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }


    //performs the image upload to the web/uploads folder
    //and sets the path property based on $user_id
    public function upload($user_id)
    {
    // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            var_dump("file was null");
            return;
        }
        var_dump("file was NOT NOT NOT null");
    // use the original file name here but you should
    // sanitize it at least to avoid any security issues

    // move takes the target directory and then the
    // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $user_id.'_'.$this->getFile()->getClientOriginalName()
            );

    // set the path property to the filename where you've saved the file
        $this->path = $user_id.'_'.$this->getFile()->getClientOriginalName();

    // clean up the file property as you won't need it anymore
        $this->file = null;

        var_dump($this->getAbsolutePath());

    }

        public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('file', new Assert\File(array(
            'maxSize' => 6000000,
        )));
    }


    public function getAbsolutePath()
    {
        return null === $this->path
        ? null
        : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
        ? null
        : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads';
    }


    /**
     * Set latitude
     *
     * @param float $latitude
     * @return Photo
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return Photo
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add tags
     *
     * @param \AppBundle\Entity\Tag $tags
     * @return Photo
     */
    public function addTag(\AppBundle\Entity\Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \AppBundle\Entity\Tag $tags
     */
    public function removeTag(\AppBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTags()
    {
        return $this->tags;
    }
}
