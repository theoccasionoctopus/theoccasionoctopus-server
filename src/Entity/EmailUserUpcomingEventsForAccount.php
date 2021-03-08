<?php

namespace App\Entity;

use App\Library;
use App\RepositoryQuery\EventRepositoryQuery;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Helper\TraitExtraFields;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmailUserUpcomingEventsForAccountRepository")
 * @ORM\Table(name="email_user_upcoming_events_for_account")
 * @ORM\HasLifecycleCallbacks()
 */
class EmailUserUpcomingEventsForAccount
{


    /**
     * @ORM\Id()
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="emailUpcomingEventsForAccount")
     */
    private $user;

    /**
     * @ORM\Id()
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="emailUsersUpcomingEvents")
     */
    private $account;


    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default" : true})
     */
    private $enabled = true;


    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $token;


    public function shouldSendIfData():bool
    {
        return $this->enabled && !$this->user->isLocked() && !$this->account->getAccountLocal()->isLocked();
    }

    public function shouldSendAndHasDataToSend($doctrine): bool
    {
        if (!$this->shouldSendIfData()) {
            return false;
        }

        // Search for events BUT only ones that start in range
        // If someone has a long event (eg all year) we don't want to trigger an email every single day
        $eventRepositoryBuilder = new EventRepositoryQuery($doctrine);
        $eventRepositoryBuilder->setAccountEvents($this->getAccount());
        $start = new \DateTime('now', $this->getAccount()->getAccountLocal()->getDefaultTimezone()->getDateTimeZoneObject());
        $start->setTime(0, 0, 0);
        $eventRepositoryBuilder->setFrom($start);
        $end = new \DateTime('now', $this->getAccount()->getAccountLocal()->getDefaultTimezone()->getDateTimeZoneObject());
        $end->setTime(23, 59, 59);
        $eventRepositoryBuilder->setTo($end);
        $eventRepositoryBuilder->setShowCancelled(false);
        $eventRepositoryBuilder->setShowDeleted(false);
        $eventRepositoryBuilder->setStartEndMode(EventRepositoryQuery::START_END_MODE_STARTING_EVENTS_ONLY);
        return (bool)$eventRepositoryBuilder->getEventOccurrences();
    }

    public function getUpcomingEventOccurrences($doctrine)
    {
        $eventRepositoryBuilder = new EventRepositoryQuery($doctrine);
        $eventRepositoryBuilder->setAccountEvents($this->getAccount());
        $start = new \DateTime('now', $this->getAccount()->getAccountLocal()->getDefaultTimezone()->getDateTimeZoneObject());
        $start->setTime(0, 0, 0);
        $eventRepositoryBuilder->setFrom($start);
        $end = new \DateTime('now', $this->getAccount()->getAccountLocal()->getDefaultTimezone()->getDateTimeZoneObject());
        $end->setTime(23, 59, 59);
        $eventRepositoryBuilder->setTo($end);
        $eventRepositoryBuilder->setShowCancelled(false);
        $eventRepositoryBuilder->setShowDeleted(false);
        // When getting events to include, don't call
        //         $eventRepositoryBuilder->setStartEndMode(EventRepositoryQuery::START_END_MODE_STARTING_EVENTS_ONLY);
        // like above. This is so events in the email include all events - ongoing and starting now.
        return $eventRepositoryBuilder->getEventOccurrences();
    }


    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->token = Library::randomString(10, 100);
    }


    /**
     * @return mixed
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
}
