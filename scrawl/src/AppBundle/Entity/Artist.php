<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Artist
 *
 * @ORM\Table(name="scrawl_artists")
 * @ORM\Entity
 */
class Artists extends User
{
    /**
    * @OneToOne(targetEntity="User", inversedby="artist")
    * @JoinColumn(name="user_id", referencedColumnName="id")
    **/
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=falase)
     */
    private $preferredMedium;

    /**
     * @ORM\Column(type=integer)
     */
    // unchanged from userId to userName
    private $confirmedBy;



}
