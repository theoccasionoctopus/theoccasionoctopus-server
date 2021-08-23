<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Library;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HistoryRepository")
 * @ORM\Table(
 *     name="history",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="history_account_slug_idx", columns={"account_id","slug"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 */
class History
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @Assert\NotNull
     * @ORM\Column(type="guid")
     */
    private $slug;


    /**
     * @Assert\NotNull
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Account", inversedBy="histories")
     */
    private $account;

    /**
     * @ORM\Column(name="created_at", type="integer", nullable=false)
     */
    private $created;


    /**
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="accounts")
     */
    private $creator;


    /**
     * @ORM\OneToMany(targetEntity="HistoryHasEvent", mappedBy="history")
     */
    private $historyHasEvents;


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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug)
    {
        $this->slug = $slug;
    }

    public function setNewIdAndSlug()
    {
        // TODO be passed doctrine manager; check Id & slug do not already exist
        $this->id = Library::GUID();
        $this->slug = $this->id;
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
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->created = time();
    }

    /**
     * @return \DateTime
     */
    public function getCreated($timezone = 'UTC'): \DateTime
    {
        $dt = new \DateTime('', new \DateTimeZone($timezone));
        $dt->setTimestamp($this->created);
        return $dt;
    }

    public function getHistoryHasEvents()
    {
        return $this->historyHasEvents;
    }
}
