<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Artists
 */
class Artists
{
    /**
     * @var string
     */
    private $preferredMedium;

    /**
     * @var string
     */
    private $confirmedBy;


    /**
     * Set preferredMedium
     *
     * @param string $preferredMedium
     * @return Artists
     */
    public function setPreferredMedium($preferredMedium)
    {
        $this->preferredMedium = $preferredMedium;

        return $this;
    }

    /**
     * Get preferredMedium
     *
     * @return string 
     */
    public function getPreferredMedium()
    {
        return $this->preferredMedium;
    }

    /**
     * Set confirmedBy
     *
     * @param string $confirmedBy
     * @return Artists
     */
    public function setConfirmedBy($confirmedBy)
    {
        $this->confirmedBy = $confirmedBy;

        return $this;
    }

    /**
     * Get confirmedBy
     *
     * @return string 
     */
    public function getConfirmedBy()
    {
        return $this->confirmedBy;
    }
}
