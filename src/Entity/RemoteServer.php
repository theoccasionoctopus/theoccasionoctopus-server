<?php

namespace App\Entity;

use App\Library;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RemoteServerRepository")
 * @ORM\Table(name="remote_server")
 * @ORM\HasLifecycleCallbacks()
 */
class RemoteServer {


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500, unique=false, nullable=false)
     */
    private $host;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ssl;

    /**
     * @ORM\Column(type="string", length=500, nullable=false)
     */
    private $title;


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
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getSSL()
    {
        return $this->ssl;
    }

    /**
     * @param mixed $ssl
     */
    public function setSSL($ssl)
    {
        $this->ssl = $ssl;
    }

    public function getURL() {
        return ($this->ssl ? 'https://' : 'http://') . $this->host;
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



}
