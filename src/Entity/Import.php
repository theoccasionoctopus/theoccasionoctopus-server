<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Helper\TraitExtraFields;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImportRepository")
 * @ORM\Table(name="import",uniqueConstraints={@ORM\UniqueConstraint(name="ical_import_account_url_idx", columns={"account_id", "url"})})
 */
class Import
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * @Assert\NotNull
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="icalimports")
     */
    private $account;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $url;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : 1})
     */
    private $enabled;

    /**
     *
     *
     * @ORM\Column(name="privacy", type="smallint", unique=false, nullable=false, options={"default" : 0})
     */
    private $privacy;

    /**
     * @var Country
     * @ORM\JoinColumn(name="default_country_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="imports")
     *
     */
    private $default_country;

    /**
     * @var TimeZone
     * @ORM\JoinColumn(name="default_timezone_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\TimeZone", inversedBy="imports")
     *
     */
    private $default_timezone;

    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

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
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * @param mixed $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * @return Country
     */
    public function getDefaultCountry(): Country
    {
        return $this->default_country;
    }

    /**
     * @param Country $default_country
     */
    public function setDefaultCountry(Country $default_country)
    {
        $this->default_country = $default_country;
    }

    /**
     * @return TimeZone
     */
    public function getDefaultTimezone(): TimeZone
    {
        return $this->default_timezone;
    }

    /**
     * @param TimeZone $default_timezone
     */
    public function setDefaultTimezone(TimeZone $default_timezone)
    {
        $this->default_timezone = $default_timezone;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }


}
