<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CountryRepository")
 * @ORM\Table(name="country")
 */
class Country
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=500, nullable=true)
     */
    private $title;


    /**
     * @ORM\Column(type="string", length=2, nullable=false, unique=true)
     */
    private $iso3166_two_char;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getIso3166TwoChar()
    {
        return $this->iso3166_two_char;
    }

    /**
     * @param mixed $iso3166_two_char
     */
    public function setIso3166TwoChar($iso3166_two_char)
    {
        $this->iso3166_two_char = $iso3166_two_char;
    }
}
