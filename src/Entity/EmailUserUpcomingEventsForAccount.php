<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Helper\TraitExtraFields;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EmailUserUpcomingEventsForAccountRepository")
 * @ORM\Table(name="email_user_upcoming_events_for_account")
 */
class EmailUserUpcomingEventsForAccount {


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


    public function shouldSendIfData():bool {
        return $this->enabled && !$this->user->isLocked();
    }

    /**
     * @return mixed
     */
    public function getUser()
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
    public function getAccount()
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
