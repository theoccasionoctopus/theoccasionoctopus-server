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
     *
     * @ORM\Column(type="string", length=500,  nullable=true)
     */
    private $username;

    /**
     * @ORM\Column(name="web_finger_data", type="json", nullable=true, options={"jsonb"=true})
     */
    private $webfingerData;


    /**
     * @ORM\Column(name="web_finger_data_last_fetched", type="integer", nullable=true)
     */
    private $webfingerDataLastFetched;


    /**
     * @ORM\Column(name="actor_data", type="json", nullable=true, options={"jsonb"=true})
     */
    private $actorData;

    /**
     * @ORM\Column(name="actor_data_last_fetched", type="integer", nullable=true)
     */
    private $actorDataLastFetched;

    /**
     *
     * @ORM\Column(name="actor_data_id", type="string", length=2000,  nullable=true, unique=true)
     */
    private $actorDataId;


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
    public function hasHumanURL()
    {
        return is_array($this->actorData) && array_key_exists('url', $this->actorData) && $this->actorData['url'];
    }

    /**
     * @return mixed
     */
    public function getHumanURL()
    {
        return $this->hasHumanURL() ? $this->actorData['url'] : null;
    }


    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getWebfingerData()
    {
        return $this->webfingerData;
    }

    public function getWebfingerDataAsString(): string
    {
        return json_encode($this->webfingerData, JSON_PRETTY_PRINT);
    }

    /**
     * @param mixed $webfingerData
     */
    public function setWebfingerData($webfingerData)
    {
        $this->webfingerData = $webfingerData;
    }

    /**
     * @return mixed
     */
    public function getWebfingerDataLastFetched()
    {
        return $this->webfingerDataLastFetched;
    }

    /**
     * @param mixed $webfingerDataLastFetched
     */
    public function setWebfingerDataLastFetched($webfingerDataLastFetched)
    {
        $this->webfingerDataLastFetched = $webfingerDataLastFetched;
    }

    /**
     * @return mixed
     */
    public function getActorData()
    {
        return $this->actorData;
    }

    public function getActorDataAsString(): string
    {
        return json_encode($this->actorData, JSON_PRETTY_PRINT);
    }

    /**
     * @param mixed $actorData
     */
    public function setActorData($actorData)
    {
        $this->actorData = $actorData;
    }

    /**
     * @return mixed
     */
    public function getActorDataLastFetched()
    {
        return $this->actorDataLastFetched;
    }

    /**
     * @param mixed $actorDataLastFetched
     */
    public function setActorDataLastFetched($actorDataLastFetched)
    {
        $this->actorDataLastFetched = $actorDataLastFetched;
    }

    /**
     * @return mixed
     */
    public function getActorDataId()
    {
        return $this->actorDataId;
    }

    /**
     * @param mixed $actorDataId
     */
    public function setActorDataId($actorDataId)
    {
        $this->actorDataId = $actorDataId;
    }
}
