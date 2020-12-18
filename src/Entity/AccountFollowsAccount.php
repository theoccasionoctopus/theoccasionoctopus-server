<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountFollowsAccountRepository")
 * @ORM\Table(name="account_follows_account")
 */
class AccountFollowsAccount
{


    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="followsAccount")
     */
    private $account;


    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="follows_account_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="followsAccountFollows")
     */
    private $followsAccount;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=false, options={"default" : true})
     */
    private $follows = true;

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
    public function getFollowsAccount()
    {
        return $this->followsAccount;
    }

    /**
     * @param mixed $followsAccount
     */
    public function setFollowsAccount($followsAccount)
    {
        $this->followsAccount = $followsAccount;
    }

    /**
     * @return bool
     */
    public function isFollows()
    {
        return $this->follows;
    }

    /**
     * @param bool $follows
     */
    public function setFollows($follows)
    {
        $this->follows = $follows;
    }
}
