<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Geolocation
 *
 * @ORM\Table(name="scrawl_geolocation")
 * @ORM\Entity
 */
class Geolocation
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10, unique=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=20, unique=false)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=20, unique=false)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=20, unique=false)
     */
    private $city;

    /**
     * @ORM\Column(type="float", unique=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", unique=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=30, unique=false)
     */
    private $streetAddress;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()parameters
    {
        return $this->id;
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
     * Set country
     *
     * @param string $country
     * @return Geolocation
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return Geolocation
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Geolocation
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