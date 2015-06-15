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
     * @ORM\Column(type="string", length=25, unique=false)
     * @ORM\Id
     */
    private $preferredMedium;

    /**
     * @ORM\Column(type="string")
     */
    // unchanged from userId to userName
    private $confirmedBy;



}
