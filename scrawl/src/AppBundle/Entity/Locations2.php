<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Location2
 *
 * @ORM\Table(name="scrawl_locations2")
 * @ORM\Entity
 */
class Locations2
{
    /**
     * @ORM\Id
     * @ORM\Column(type="float", nullable=false)
     */
    private $latitude;

    /**
     * 
     * @ORM\Column(type="float", nullable=false)
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=10, unique=true, nullable=false)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=30, unique=false, nullable=false)
     */
    private $streetAddress;


    /**
     * Set latitude
     *
     * @param float $latitude
     * @return Geolocation
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
     * @return Geolocation
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
     * Set postalCode
     *
     * @param string $postalCode
     * @return Geolocation
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string 
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set streetAddress
     *
     * @param string $streetAddress
     * @return Geolocation
     */
    public function setStreetAddress($streetAddress)
    {
        $this->streetAddress = $streetAddress;

        return $this;
    }

    /**
     * Get streetAddress
     *
     * @return string 
     */
    public function getStreetAddress()
    {
        return $this->streetAddress;
    }
}
