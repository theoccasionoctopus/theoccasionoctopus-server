<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HistoryHasTagRepository")
 * @ORM\Table(name="history_has_tag")
 */
class HistoryHasTag
{


    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="history_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\History", inversedBy="histories")
     */
    private $history;


    /**
     * @ORM\Id()
     * @Assert\NotNull
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id", nullable=false)
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag", inversedBy="histories")
     */
    private $tag;


    /**
     * @return mixed
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param mixed $history
     */
    public function setHistory($history)
    {
        $this->history = $history;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param mixed $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }
}
