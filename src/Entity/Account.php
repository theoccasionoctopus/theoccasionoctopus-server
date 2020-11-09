<?php

namespace App\Entity;

use App\Library;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 * @ORM\Table(name="account")
 * @ORM\HasLifecycleCallbacks()
 */
class Account
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="AccountLocal", mappedBy="account")
     */
    private $accountLocal;

    /**
     * @ORM\OneToOne(targetEntity="AccountRemote", mappedBy="account")
     */
    private $accountRemote;

    /**
     * @ORM\Column(type="string", length=500, nullable=false)
     */
    private $title;

    /**
     *
     *
     * @ORM\Column(name="years_behind", type="smallint", unique=false, nullable=false, options={"default" : 10})
     */
    private $years_behind = 10;

    /**
     *
     *
     * @ORM\Column(name="years_ahead", type="smallint", unique=false, nullable=false, options={"default" : 10})
     */
    private $years_ahead = 10;

    /**
     * @ORM\Column(name="created_at", type="integer", nullable=false)
     */
    private $created;

    /**
     * @ORM\Column(name="limit_number_of_events", type="integer", nullable=false, options={"default" : 100000})
     * TODO Actually check this, in web UI and API
     */
    private $limitNumberOfEvents = 100000;

    /**
     * @ORM\Column(name="limit_number_of_event_occurrences", type="integer", nullable=false, options={"default" : 100000})
     * TODO Actually check this, in web UI and API
     */
    private $limitNumberOfEventOccurrences = 100000;

    /**
     * @ORM\Column(name="limit_number_of_tags", type="integer", nullable=false, options={"default" : 100000})
     * TODO Actually check this, in web UI and API
     */
    private $limitNumberOfTags = 100000;

    /**
     * @ORM\Column(name="limit_number_of_accounts_following", type="integer", nullable=false, options={"default" : 10000})
     * TODO Actually check this, in web UI and API
     */
    private $limitNumberOfAccountsFollowing = 10000;

    /**
     * @ORM\Column(name="limit_number_of_imports", type="integer", nullable=false, options={"default" : 100})
     * TODO Actually check this, in web UI and API
     */
    private $limitNumberOfImports = 100;

    /**
     * @ORM\OneToMany(targetEntity="AccountFollowsAccount", mappedBy="account")
     */
    private $followsAccount;



    /**
     * @ORM\OneToMany(targetEntity="AccountFollowsAccount", mappedBy="followsAccount")
     */
    private $followsAccountFollows;

    /**
     * @ORM\OneToMany(targetEntity="UserManageAccount", mappedBy="account")
     */
    private $managedByUser;

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
    public function getAccountLocal()
    {
        return $this->accountLocal;
    }

    /**
     * @return mixed
     */
    public function getAccountRemote()
    {
        return $this->accountRemote;
    }


    public function getUsername() {
        return $this->accountLocal->getUsername();
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
    public function getYearsAhead()
    {
        return $this->years_ahead;
    }

    /**
     * @param mixed $years_ahead
     */
    public function setYearsAhead($years_ahead)
    {
        $this->years_ahead = $years_ahead;
    }

    /**
     * @return mixed
     */
    public function getYearsBehind()
    {
        return $this->years_behind;
    }

    /**
     * @param mixed $years_behind
     */
    public function setYearsBehind($years_behind)
    {
        $this->years_behind = $years_behind;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->created = time();
    }

    /**
     * @return mixed
     */
    public function getLimitNumberOfEvents()
    {
        return $this->limitNumberOfEvents;
    }

    /**
     * @return mixed
     */
    public function getLimitNumberOfEventOccurrences()
    {
        return $this->limitNumberOfEventOccurrences;
    }

    /**
     * @return mixed
     */
    public function getLimitNumberOfTags()
    {
        return $this->limitNumberOfTags;
    }

    /**
     * @return mixed
     */
    public function getLimitNumberOfAccountsFollowing()
    {
        return $this->limitNumberOfAccountsFollowing;
    }

    /**
     * @return mixed
     */
    public function getLimitNumberOfImports()
    {
        return $this->limitNumberOfImports;
    }



}
