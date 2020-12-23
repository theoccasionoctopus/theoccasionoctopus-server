<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Helper\TraitExtraFields;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InboxSubmissionRepository")
 * @ORM\Table(name="inbox_submission")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class InboxSubmission
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @Assert\NotNull
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="inbox_submissions")
     */
    private $account;

    /**
     * @ORM\Column(type="json", nullable=false, options={"jsonb"=true})
     */
    private $data;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @Assert\Ip(version="all")
     */
    private $ip;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    private $useragent;

    /**
     * @ORM\Column(name="created_at", type="integer", nullable=false)
     */
    private $created;

    /**
     * @ORM\Column(name="processed_at", type="integer", nullable=true)
     */
    private $processed;

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
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return mixed
     */
    public function getUseragent()
    {
        return $this->useragent;
    }

    /**
     * @param mixed $useragent
     */
    public function setUseragent($useragent)
    {
        $this->useragent = $useragent;
    }

    /**
     * @return mixed
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * @param mixed $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }
}
