<?php

namespace App\Entity;

use App\Library;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRemoteRepository")
 * @ORM\Table(name="account_remote")
 * @ORM\HasLifecycleCallbacks()
 */
class AccountRemote
{

    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * @ORM\OneToOne(targetEntity="App\Entity\Account")
     */
    private $account;

    /**
     * @var RemoteServer
     * @ORM\JoinColumn(name="remote_server_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\RemoteServer", inversedBy="accountRemotes")
     *
     */
    private $remoteServer;

    /**
     * @ORM\Column(name="human_url", type="text", nullable=false)
     */
    private $humanURL;

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
     * @return RemoteServer
     */
    public function getRemoteServer(): RemoteServer
    {
        return $this->remoteServer;
    }

    /**
     * @param RemoteServer $remoteServer
     */
    public function setRemoteServer(RemoteServer $remoteServer)
    {
        $this->remoteServer = $remoteServer;
    }

    /**
     * @return mixed
     */
    public function getHumanURL()
    {
        return $this->humanURL;
    }

    /**
     * @param mixed $humanURL
     */
    public function setHumanURL($humanURL)
    {
        $this->humanURL = $humanURL;
    }
}
