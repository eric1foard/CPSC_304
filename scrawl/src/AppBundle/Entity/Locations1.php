<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Locations1
 *
 * @ORM\Table(name="scrawl_locations1")
 * @ORM\Entity
 */
class Locations1
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=10, unique=true, nullable=false)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=20, unique=false, nullable=false)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=20, unique=false, nullable=false)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=20, unique=false, nullable=false)
     */
    private $city;


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
}
