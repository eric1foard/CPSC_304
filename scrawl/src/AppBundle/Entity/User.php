<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="scrawl_users")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @Assert\Length(min = "8")
     * @ORM\Column(name="password_hash", type="string", length=129, nullable=false)
     * @Assert\NotBlank(message="Password may not be empty")
     * @Assert\Length(
     *      min = "5",
     *      minMessage = "Password must be at least 5 characters long",
     * )
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="password_salt", type="string", length=40, nullable=false)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=60, unique=false)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=256, unique=false)
     */
    private $selfSummary;


    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Photo", mappedBy="user")
     **/
    private $photos;




    public function __construct()
    {
        $this->salt = md5(uniqid("p_", true) . time());

    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return $this->salt;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
            ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->salt
            ) = unserialize($serialized);
    }

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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return User
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set selfSummary
     *
     * @param string $selfSummary
     * @return User
     */
    public function setSelfSummary($selfSummary)
    {
        $this->selfSummary = $selfSummary;

        return $this;
    }

    /**
     * Get selfSummary
     *
     * @return string 
     */
    public function getSelfSummary()
    {
        return $this->selfSummary;
    }

    /**
     * Add photos
     *
     * @param \AppBundle\Entity\Photo $photos
     * @return User
     */
    public function addPhoto(\AppBundle\Entity\Photo $photos)
    {
        $this->photos[] = $photos;

        return $this;
    }

    /**
     * Remove photos
     *
     * @param \AppBundle\Entity\Photo $photos
     */
    public function removePhoto(\AppBundle\Entity\Photo $photos)
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
