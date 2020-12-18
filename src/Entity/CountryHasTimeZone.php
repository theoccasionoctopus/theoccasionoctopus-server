<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CountryHasTimeZoneRepository")
 * @ORM\Table(name="Country_has_timezone")
 */
class CountryHasTimeZone
{

    /**
     * @var Country
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="links")
     */
    private $country;

    /**
     * @var TimeZone
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="timezone_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\TimeZone", inversedBy="links")
     */
    private $timezone;

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param mixed $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }
}
