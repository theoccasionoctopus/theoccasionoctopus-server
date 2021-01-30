<?php

namespace App\Entity;

use App\Library;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Helper\TraitExtraFields;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RemoteServerSendDataRepository")
 * @ORM\Table(name="remote_server_send_data")
 * @ORM\HasLifecycleCallbacks()
 */
class RemoteServerSendData
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="guid")
     */
    private $id;

    /**
     * This should always be a accountLocal object
     * @Assert\NotNull
     * @ORM\JoinColumn(name="from_account_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="sendtoactivitypubservers")
     */
    private $fromAccount;


    /**
     * This should always be a accountRemote object
     * @Assert\NotNull
     * @ORM\JoinColumn(name="to_account_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="sendtoactivitypubservers")
     */
    private $toAccount;

    /**
     * @ORM\Column(type="json", nullable=true, options={"jsonb"=true}, nullable=false)
     */
    private $data;

    /**
     * @ORM\Column(name="created_at", type="integer", nullable=false)
     */
    private $created;

    /**
     * @ORM\Column(name="succeeded_at", type="integer", nullable=true)
     */
    private $succeeded;

    /**
     * @ORM\Column(name="failed_count", type="integer", nullable=false, options={"default" : 0})
     */
    private $failedCount = 0;

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


    public function getFromAccount(): Account
    {
        return $this->fromAccount;
    }

    public function setFromAccount(Account $fromAccount)
    {
        $this->fromAccount = $fromAccount;
    }

    public function getToAccount(): Account
    {
        return $this->toAccount;
    }

    public function setToAccount(Account $toAccount)
    {
        $this->toAccount = $toAccount;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getSucceeded()
    {
        return $this->succeeded;
    }

    /**
     * @param mixed $succeeded
     */
    public function setSucceededNow()
    {
        $this->succeeded = time();
    }

    /**
     * @return mixed
     */
    public function getFailedCount()
    {
        return $this->failedCount;
    }

    public function increaseFailedCount()
    {
        $this->failedCount += 1;
    }

    /**
     * @ORM\PrePersist
     */
    public function setPrePersistValues()
    {
        $this->id = Library::GUID();
        $this->created = time();
    }
}
